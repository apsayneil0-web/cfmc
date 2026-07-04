@extends('manager.layout')

@section('title', 'Reporting')
@section('header', 'Reporting Module')

@section('content')
<!-- Report Type Selection -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center mb-4 group-hover:bg-green-200">
            <i class="fas fa-seedling text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Harvesting Reports</h3>
        <p class="text-sm text-gray-500">Track harvest yields, crop types, and production data</p>
    </button>

    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center mb-4 group-hover:bg-blue-200">
            <i class="fas fa-hand-holding-usd text-blue-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Loan Reports</h3>
        <p class="text-sm text-gray-500">Loan disbursements, repayments, and outstanding balances</p>
    </button>

    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="w-14 h-14 rounded-xl bg-yellow-100 flex items-center justify-center mb-4 group-hover:bg-yellow-200">
            <i class="fas fa-wrench text-yellow-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Maintenance Reports</h3>
        <p class="text-sm text-gray-500">Machinery maintenance history and usage tracking</p>
    </button>
</div>

<!-- Report Generator -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Generate Report</h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option>Harvesting Report</option>
                <option>Loan Report</option>
                <option>Maintenance Report</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Member</label>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option>All Members</option>
                <option>Juan Dela Cruz</option>
                <option>Maria Santos</option>
                <option>Pedro Reyes</option>
            </select>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-filter mr-2"></i>Generate
        </button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-print mr-2"></i>Print
        </button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </button>
    </div>
</div>

<!-- Sample Report Preview -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Report Preview</h3>
    </div>
    <div class="p-6">
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <i class="fas fa-chart-bar text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Select filters and click "Generate" to view report</p>
        </div>
    </div>
</div>

<!-- Dashboard Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Harvest Yield Chart</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500"><i class="fas fa-chart-bar text-3xl mb-2"></i><br>Chart visualization</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Status Chart</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500"><i class="fas fa-chart-pie text-3xl mb-2"></i><br>Chart visualization</p>
        </div>
    </div>
</div>
@endsection