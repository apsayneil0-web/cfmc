@extends('manager.layout')

@section('title', 'Financial Management')
@section('header', 'Financial Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Operational Expenses</p>
        <p class="text-2xl font-bold text-gray-900">₱245,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Machinery Expenditures</p>
        <p class="text-2xl font-bold text-gray-900">₱180,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Harvest Income</p>
        <p class="text-2xl font-bold text-green-600">₱890,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Cooperative Share</p>
        <p class="text-2xl font-bold text-blue-600">₱89,000</p>
        <p class="text-xs text-gray-400">9% - 12%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Net Income</p>
        <p class="text-2xl font-bold text-emerald-600">₱376,000</p>
    </div>
</div>

<!-- Charts and Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Monthly Income/Expense Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Financial Overview</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500"><i class="fas fa-chart-bar text-4xl mb-2"></i><br>Chart visualization</p>
        </div>
    </div>

    <!-- Income Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Breakdown</h3>
        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Machine Rental</span>
                    <span class="text-sm font-medium text-gray-900">₱450,000</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 50%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Harvest Sales</span>
                    <span class="text-sm font-medium text-gray-900">₱320,000</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: 36%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">CBU Interest</span>
                    <span class="text-sm font-medium text-gray-900">₱85,000</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: 10%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Loan Interest</span>
                    <span class="text-sm font-medium text-gray-900">₱35,000</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-600 h-2 rounded-full" style="width: 4%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense Reports Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Expense Reports</h3>
        <div class="flex items-center gap-3">
            <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-file-export mr-2"></i>Export
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Fuel - Tractor #001</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Machinery</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱12,500</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 02, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maintenance - Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Machinery</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱25,000</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 05, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Office Supplies</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Operational</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱5,500</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Paid</span>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 10, 2026</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Operator Salary</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Operational</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱15,000</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection