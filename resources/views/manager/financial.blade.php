@extends('manager.layout')

@section('title', 'Financial Management')
@section('header', 'Financial Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <x-stat-card label="Operational Expenses" value="₱245,000" icon="fa-file-invoice" color="secondary" />
    <x-stat-card label="Machinery Expenditures" value="₱180,000" icon="fa-tractor" color="warning" />
    <x-stat-card label="Harvest Income" value="₱890,000" icon="fa-seedling" color="success" />
    <x-stat-card label="Cooperative Share" value="₱89,000" icon="fa-hand-holding-usd" color="primary" trend="9% - 12%" />
    <x-stat-card label="Net Income" value="₱376,000" icon="fa-chart-line" color="success" />
</div>

<!-- Charts and Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Monthly Income/Expense Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Financial Overview</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <p class="text-gray-500 text-center"><i class="fas fa-chart-bar text-4xl mb-2 d-block"></i>Chart visualization</p>
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
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-primary" style="width: 50%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Harvest Sales</span>
                    <span class="text-sm font-medium text-gray-900">₱320,000</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-success" style="width: 36%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">CBU Interest</span>
                    <span class="text-sm font-medium text-gray-900">₱85,000</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-purple" style="width: 10%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Loan Interest</span>
                    <span class="text-sm font-medium text-gray-900">₱35,000</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-warning" style="width: 4%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expense Reports Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Expense Reports</h3>
            <input type="date" class="form-control" style="width: auto;">
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i><span>Filter</span>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-file-export"></i><span>Export</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Category</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4">Fuel - Tractor #001</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Machinery</td>
                    <td class="px-4 px-md-6 py-4">₱12,500</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Paid" /></td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 02, 2026</td>
                    <td class="px-4 px-md-6 py-4">Maintenance - Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Machinery</td>
                    <td class="px-4 px-md-6 py-4">₱25,000</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Paid" /></td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 05, 2026</td>
                    <td class="px-4 px-md-6 py-4">Office Supplies</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Operational</td>
                    <td class="px-4 px-md-6 py-4">₱5,500</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Paid" /></td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 10, 2026</td>
                    <td class="px-4 px-md-6 py-4">Operator Salary</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Operational</td>
                    <td class="px-4 px-md-6 py-4">₱15,000</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
