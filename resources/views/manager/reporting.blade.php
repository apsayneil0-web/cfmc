@extends('manager.layout')

@section('title', 'Reporting')
@section('header', 'Reporting Module')

@section('content')
<!-- Report Type Selection -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
    <a href="{{ route('manager.reporting', ['report_type' => 'harvesting']) }}"
        class="bg-white rounded-xl shadow-sm border p-6 text-left text-decoration-none transition {{ $reportType === 'harvesting' ? 'border-primary ring-2 ring-primary-subtle' : 'border-gray-200' }}">
        <div class="stat-icon text-success mb-4">
            <i class="fas fa-seedling"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Harvesting Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Track harvest yields, crop types, and production data</p>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'loan']) }}"
        class="bg-white rounded-xl shadow-sm border p-6 text-left text-decoration-none transition {{ $reportType === 'loan' ? 'border-primary ring-2 ring-primary-subtle' : 'border-gray-200' }}">
        <div class="stat-icon text-primary mb-4">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Loan Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Loan disbursements, repayments, and outstanding balances</p>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'maintenance']) }}"
        class="bg-white rounded-xl shadow-sm border p-6 text-left text-decoration-none transition {{ $reportType === 'maintenance' ? 'border-primary ring-2 ring-primary-subtle' : 'border-gray-200' }}">
        <div class="stat-icon text-warning mb-4">
            <i class="fas fa-wrench"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Maintenance Reports</h3>
        <p class="text-sm text-gray-500 mb-0">Machinery maintenance history and usage tracking</p>
    </a>
</div>

<!-- Report Generator -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 no-print">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Generate Report</h3>

    <form action="{{ route('manager.reporting') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label fw-semibold">Report Type</label>
                <select name="report_type" class="form-select">
                    <option value="harvesting" @selected($reportType === 'harvesting')>Harvesting Report</option>
                    <option value="loan" @selected($reportType === 'loan')>Loan Report</option>
                    <option value="maintenance" @selected($reportType === 'maintenance')>Maintenance Report</option>
                </select>
            </div>
            <div>
                <label class="form-label fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div>
                <label class="form-label fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div>
                <label class="form-label fw-semibold">Filter by Member</label>
                <select name="member_id" class="form-select" {{ $reportType === 'maintenance' ? 'disabled' : '' }}>
                    <option value="">All Members</option>
                    @foreach($farmers as $farmer)
                    <option value="{{ $farmer->id }}" @selected((string) $memberId === (string) $farmer->id)>{{ $farmer->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i><span>Generate</span>
            </button>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-print"></i><span>Print / Save as PDF</span>
            </button>
            <a href="{{ route('manager.reporting.export', request()->query()) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-file-csv"></i><span>Export CSV</span>
            </a>
        </div>
    </form>
</div>

<!-- Report Preview -->
<div class="section-card mt-6">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">
            {{ match($reportType) { 'harvesting' => 'Harvesting Report', 'loan' => 'Loan Report', 'maintenance' => 'Maintenance Report' } }}
        </h3>
    </div>

    @if($reportType === 'harvesting')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Size</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Harvest Yield</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->display_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->land_size }} ha</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $row->harvest_yield }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->location }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No harvest records match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'loan')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Principal</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Remaining Balance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Disbursed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-{{ str_pad($row->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->farmer?->full_name }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($row->principal_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $row->remaining_balance !== null ? peso($row->remaining_balance) : '—' }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst(str_replace('_', ' ', $row->status))" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->disbursed_at?->format('M d, Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No loans match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Times Used (in range)</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Hours (in range)</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Maintenance Tier</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $row->machine->name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->times_used }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->usage_hours }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$row->machine->maintenance_label" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 px-md-6 py-6 text-center text-muted">No machinery on file.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- Breakdown -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        {{ match($reportType) { 'harvesting' => 'Top Yield by Farmer', 'loan' => 'Loan Status Breakdown', 'maintenance' => 'Maintenance Tier Breakdown' } }}
    </h3>
    @php $breakdownTotal = $breakdown->sum('value'); @endphp
    <div class="space-y-4">
        @forelse($breakdown as $item)
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600">{{ $item->label }}</span>
                <span class="text-sm font-medium text-gray-900">{{ $item->value }}</span>
            </div>
            <div class="progress" style="height: 0.5rem;">
                <div class="progress-bar bg-primary" style="width: {{ $breakdownTotal > 0 ? round(($item->value / $breakdownTotal) * 100) : 0 }}%"></div>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No data to break down for these filters.</p>
        @endforelse
    </div>
</div>

<style>
    @media print {
        .no-print, .app-sidebar, .app-topbar { display: none !important; }
    }
</style>
@endsection
