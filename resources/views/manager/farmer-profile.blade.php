@extends('manager.layout')

@section('title', 'Farmer Profile Management')
@section('header', 'Farmer Profile Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search farmers..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="locked">Locked</option>
                <option value="archived">Archived</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Register Farmer
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Membership Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Crop Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Land Area</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">JD</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Juan Dela Cruz</p>
                                <p class="text-xs text-gray-500">Member ID: FM-001</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">0912-345-6789</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Rice</td>
                    <td class="px-6 py-4 text-sm text-gray-500">2.5 ha</td>
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
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Lock">
                                <i class="fas fa-lock"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-medium">MS</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Maria Santos</p>
                                <p class="text-xs text-gray-500">Member ID: FM-002</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">0913-456-7890</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Corn</td>
                    <td class="px-6 py-4 text-sm text-gray-500">1.8 ha</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Locked</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Unlock">
                                <i class="fas fa-unlock"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-medium">PR</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Pedro Reyes</p>
                                <p class="text-xs text-gray-500">Member ID: FM-003</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Archived</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">0914-567-8901</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Vegetables</td>
                    <td class="px-6 py-4 text-sm text-gray-500">1.2 ha</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-gray-400 cursor-not-allowed" disabled>
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1-10 of 156 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>

<!-- View/Edit Modal -->
<div id="viewModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Farmer Profile Details</h3>
            <button onclick="document.getElementById('viewModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6 space-y-6">
            <!-- Personal Information -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Personal Information</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Full Name</label>
                        <p class="text-sm font-medium text-gray-900">Juan Dela Cruz</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Date of Birth</label>
                        <p class="text-sm font-medium text-gray-900">January 15, 1985</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Contact Number</label>
                        <p class="text-sm font-medium text-gray-900">0912-345-6789</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Barangay</label>
                        <p class="text-sm font-medium text-gray-900">Brgy. San Jose</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Email</label>
                        <p class="text-sm font-medium text-gray-900">juan.delacruz@email.com</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Member ID</label>
                        <p class="text-sm font-medium text-gray-900">FM-001</p>
                    </div>
                </div>
            </div>

            <!-- Farming Information -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Farming Information</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Crop Type</label>
                        <p class="text-sm font-medium text-gray-900">Rice</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Land Area</label>
                        <p class="text-sm font-medium text-gray-900">2.5 hectares</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Farm Location</label>
                        <p class="text-sm font-medium text-gray-900">Brgy. San Jose</p>
                    </div>
                </div>
            </div>

            <!-- Membership & Payment -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Membership & Payment Details</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Membership Status</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">CBU Balance</label>
                        <p class="text-sm font-medium text-gray-900">₱12,500.00</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Total Savings</label>
                        <p class="text-sm font-medium text-gray-900">₱45,000.00</p>
                    </div>
                </div>
            </div>

            <!-- Login Credentials -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Login Credentials</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Username</label>
                        <p class="text-sm font-medium text-gray-900">juan.dc</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Account Status</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection