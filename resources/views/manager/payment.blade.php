@extends('manager.layout')

@section('title', 'Payments')
@section('header', 'Payment Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Loan Payments</p>
        <p class="text-2xl font-bold text-gray-900">₱125,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">CBU Contributions</p>
        <p class="text-2xl font-bold text-blue-600">₱85,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Operational Expenses</p>
        <p class="text-2xl font-bold text-yellow-600">₱45,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Replaceable Parts</p>
        <p class="text-2xl font-bold text-red-600">₱22,500</p>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search payments..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option>Loan Payment</option>
                <option>CBU Contribution</option>
                <option>Operational Expense</option>
                <option>Replaceable Parts</option>
            </select>
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Record Payment
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payor/Payer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">PAY-001</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Loan Payment</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">LN-002</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱5,000</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
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
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">PAY-002</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">CBU Contribution</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">FM-001</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱1,000</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
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
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">PAY-003</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 02, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Office Operations</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Operational Expense</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">-</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱5,500</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
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
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">PAY-004</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 03, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maintenance Dept</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Replaceable Parts</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">-</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱12,500</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Completed</span>
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
        <p class="text-sm text-gray-500">Showing 1-10 of 45 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>

<!-- Auto-Update Info -->
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-6">
    <div class="flex items-center gap-3">
        <i class="fas fa-sync-alt text-green-600 text-xl"></i>
        <div>
            <p class="text-sm font-medium text-green-900">Automatic Updates</p>
            <p class="text-xs text-green-700">Payments automatically update loan balance, CBU balance, and financial records.</p>
        </div>
    </div>
</div>
@endsection