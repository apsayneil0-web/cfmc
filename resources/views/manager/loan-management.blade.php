@extends('manager.layout')

@section('title', 'Loan Management')
@section('header', 'Loan Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Active Loans</p>
        <p class="text-3xl font-bold text-gray-900">12</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Total Outstanding</p>
        <p class="text-3xl font-bold text-red-600">₱450,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Due This Month</p>
        <p class="text-3xl font-bold text-yellow-600">₱85,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Interest Earned</p>
        <p class="text-3xl font-bold text-green-600">₱35,000</p>
    </div>
</div>

<!-- Active Loans Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Active Loans</h3>
        <div class="flex items-center gap-3">
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="current">Current</option>
                <option value="overdue">Overdue</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Principal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remaining Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Due</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Due</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interest (2%)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱30,000</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱24,500</td>
                    <td class="px-6 py-4 text-sm text-gray-500">₱5,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 15, 2026</td>
                    <td class="px-6 py-4 text-sm text-yellow-600">+₱490</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Current</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-004</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Roberto Tan</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱100,000</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱85,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">₱8,333</td>
                    <td class="px-6 py-4 text-sm text-red-600">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm text-yellow-600">+₱1,700</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Overdue</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-005</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Ana Garcia</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱45,000</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱37,500</td>
                    <td class="px-6 py-4 text-sm text-gray-500">₱3,750</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 20, 2026</td>
                    <td class="px-6 py-4 text-sm text-yellow-600">+₱750</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Current</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
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

<!-- Interest Computation Info -->
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-6">
    <div class="flex items-center gap-3">
        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
        <div>
            <p class="text-sm font-medium text-blue-900">Interest Computation: 2% every due date</p>
            <p class="text-xs text-blue-700">Interest is automatically calculated and added to the loan balance on each due date.</p>
        </div>
    </div>
</div>
@endsection