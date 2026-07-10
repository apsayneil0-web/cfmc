@extends('manager.layout')

@section('title', 'Reporting')
@section('header', 'Reporting Module')

@section('content')
<!-- Report Type Selection -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="stat-icon text-success mb-4">
            <i class="fas fa-seedling"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Harvesting Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Track harvest yields, crop types, and production data</p>
    </button>

    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="stat-icon text-primary mb-4">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Loan Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Loan disbursements, repayments, and outstanding balances</p>
    </button>

    <button class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-left hover:border-blue-500 hover:ring-2 hover:ring-blue-200 transition group">
        <div class="stat-icon text-warning mb-4">
            <i class="fas fa-wrench"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Maintenance Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Machinery maintenance history and usage tracking</p>
    </button>
</div>

<!-- Report Generator -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Generate Report</h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div>
            <label class="form-label fw-semibold">Report Type</label>
            <select class="form-select">
                <option>Harvesting Report</option>
                <option>Loan Report</option>
                <option>Maintenance Report</option>
            </select>
        </div>
        <div>
            <label class="form-label fw-semibold">Date From</label>
            <input type="date" class="form-control">
        </div>
        <div>
            <label class="form-label fw-semibold">Date To</label>
            <input type="date" class="form-control">
        </div>
        <div>
            <label class="form-label fw-semibold">Filter by Member</label>
            <select class="form-select">
                <option>All Members</option>
                <option>Juan Dela Cruz</option>
                <option>Maria Santos</option>
                <option>Pedro Reyes</option>
            </select>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <button class="btn btn-primary d-flex align-items-center gap-2">
            <i class="fas fa-filter"></i><span>Generate</span>
        </button>
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="fas fa-print"></i><span>Print</span>
        </button>
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="fas fa-file-pdf"></i><span>Export PDF</span>
        </button>
        <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="fas fa-file-excel"></i><span>Export Excel</span>
        </button>
    </div>
</div>

<!-- Sample Report Preview -->
<div class="section-card mt-6">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Report Preview</h3>
    </div>
    <div class="p-4 p-md-6">
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <i class="fas fa-chart-bar text-gray-300 mb-3 d-block" style="font-size: 3.5rem;"></i>
            <p class="text-gray-500 mb-0">Select filters and click "Generate" to view report</p>
        </div>
    </div>
</div>

<!-- Dashboard Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Harvest Yield Chart</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500 text-center mb-0"><i class="fas fa-chart-bar text-3xl mb-2 d-block"></i>Chart visualization</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Status Chart</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500 text-center mb-0"><i class="fas fa-chart-pie text-3xl mb-2 d-block"></i>Chart visualization</p>
        </div>
    </div>
</div>
@endsection
