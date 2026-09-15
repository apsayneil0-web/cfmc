@extends('manager.layout')

@section('title', 'Reporting')
@section('header', 'Reporting Module')

@section('content')
<!-- Report Type Selection -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 no-print">
    <a href="{{ route('manager.reporting', ['report_type' => 'harvesting']) }}"
        class="report-type-card {{ $reportType === 'harvesting' ? 'active' : '' }}" title="Track harvest yields, crop types, and production data">
        <div class="report-type-icon bg-success-subtle text-success"><i class="fas fa-seedling"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Harvesting</div>
            <div class="report-type-desc">Yields &amp; production data</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'loan']) }}"
        class="report-type-card {{ $reportType === 'loan' ? 'active' : '' }}" title="Loan disbursements, repayments, and outstanding balances">
        <div class="report-type-icon bg-primary-subtle text-primary"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Loan</div>
            <div class="report-type-desc">Disbursements &amp; balances</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'maintenance']) }}"
        class="report-type-card {{ $reportType === 'maintenance' ? 'active' : '' }}" title="Machinery maintenance history and usage tracking">
        <div class="report-type-icon bg-warning-subtle text-warning"><i class="fas fa-wrench"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Maintenance</div>
            <div class="report-type-desc">Machinery usage &amp; tiers</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'schedule']) }}"
        class="report-type-card {{ $reportType === 'schedule' ? 'active' : '' }}" title="Members vs non-members who scheduled machinery in a month">
        <div class="report-type-icon bg-info-subtle text-info"><i class="fas fa-calendar-check"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Schedule</div>
            <div class="report-type-desc">Members vs non-members</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'cbu']) }}"
        class="report-type-card {{ $reportType === 'cbu' ? 'active' : '' }}" title="Member savings, capital contributions, and CBU activity">
        <div class="report-type-icon bg-success-subtle text-success"><i class="fas fa-piggy-bank"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Capital Build-Up</div>
            <div class="report-type-desc">Savings &amp; contributions</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'complaint']) }}"
        class="report-type-card {{ $reportType === 'complaint' ? 'active' : '' }}" title="Farmer complaints, resolution status, and common problems">
        <div class="report-type-icon bg-danger-subtle text-danger"><i class="fas fa-comment-dots"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Complaint</div>
            <div class="report-type-desc">Status &amp; common problems</div>
        </div>
    </a>

    <a href="{{ route('manager.reporting', ['report_type' => 'expense']) }}"
        class="report-type-card {{ $reportType === 'expense' ? 'active' : '' }}" title="Machine-related operational expenses and maintenance costs">
        <div class="report-type-icon bg-warning-subtle text-warning"><i class="fas fa-receipt"></i></div>
        <div class="report-type-text">
            <div class="report-type-title">Expense</div>
            <div class="report-type-desc">Operational &amp; machine costs</div>
        </div>
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
                    <option value="schedule" @selected($reportType === 'schedule')>Schedule Report</option>
                    <option value="cbu" @selected($reportType === 'cbu')>Capital Build-Up Report</option>
                    <option value="complaint" @selected($reportType === 'complaint')>Complaint Report</option>
                    <option value="expense" @selected($reportType === 'expense')>Expense Report</option>
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
                <select name="member_id" id="reportMemberSelect" class="form-select searchable-select" data-placeholder="Search farmer by name..." {{ in_array($reportType, ['maintenance', 'expense']) ? 'disabled' : '' }}>
                    <option value="">All Members</option>
                    @foreach($farmers as $farmer)
                    <option value="{{ $farmer->id }}" @selected((string) $memberId === (string) $farmer->id)>{{ $farmer->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($reportType === 'loan')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="form-label fw-semibold">Loan Type</label>
                <input type="text" name="loan_type" class="form-control" placeholder="e.g. Production, Machinery..." value="{{ $loanType }}">
            </div>
            <div>
                <label class="form-label fw-semibold">Loan Status</label>
                <select name="loan_status" class="form-select">
                    <option value="" @selected(! $loanStatus)>All</option>
                    <option value="active" @selected($loanStatus === 'active')>Active</option>
                    <option value="overdue" @selected($loanStatus === 'overdue')>Overdue</option>
                    <option value="fully_paid" @selected($loanStatus === 'fully_paid')>Fully Paid</option>
                </select>
            </div>
            <div>
                <label class="form-label fw-semibold">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="" @selected(! $paymentStatus)>All</option>
                    <option value="unpaid" @selected($paymentStatus === 'unpaid')>Unpaid</option>
                    <option value="partial" @selected($paymentStatus === 'partial')>Partial</option>
                    <option value="paid" @selected($paymentStatus === 'paid')>Paid</option>
                </select>
            </div>
        </div>
        @endif

        @if($reportType === 'expense')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label fw-semibold">Category</label>
                <select name="category" class="form-select">
                    <option value="" @selected(! $category)>All Categories</option>
                    <option value="operational" @selected($category === 'operational')>Operational</option>
                    <option value="machinery" @selected($category === 'machinery')>Machinery</option>
                    <option value="replaceable_parts" @selected($category === 'replaceable_parts')>Replaceable Parts</option>
                </select>
            </div>
        </div>
        @endif

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

@php
    $reportTitle = match($reportType) {
        'harvesting' => 'Harvesting Report',
        'loan' => 'Loan Report',
        'maintenance' => 'Maintenance Report',
        'schedule' => 'Schedule Report',
        'cbu' => 'Capital Build-Up Report',
        'complaint' => 'Complaint Report',
        'expense' => 'Expense Report',
    };
@endphp

<!-- Print Letterhead (screen-hidden, shown only when printing/exporting to PDF) -->
<div class="print-only print-letterhead">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="print-logo-circle"><i class="fas fa-seedling"></i></div>
        <div>
            <h2 class="mb-0 fw-bold" style="color:#1f5c3a;">CENTRALA FARMERS MARKETING COOPERATIVE</h2>
            <p class="mb-0 text-muted">Centrala, Surallah, South Cotabato</p>
        </div>
    </div>

    <hr style="border-top: 2px solid #1f5c3a; margin: 1rem 0;">

    <h1 class="text-center fw-bold mb-1">{{ strtoupper($reportTitle) }}</h1>
    <p class="text-center text-muted mb-4">
        <em>Report Generated: {{ now()->format('F j, Y') }}</em> &nbsp;|&nbsp; <em>Generated by: <strong>{{ auth()->user()->name }}</strong></em>
    </p>

    <div class="print-filter-box mb-4">
        <div class="row">
            <div class="col-6">
                <p class="mb-1"><strong>Date Range</strong> &nbsp; From: {{ $dateFrom ?: '—' }} &nbsp; To: {{ $dateTo ?: '—' }}</p>
                @if($reportType !== 'expense')
                <p class="mb-1"><strong>Farmer</strong> &nbsp; {{ $selectedFarmer?->full_name ?? 'All Farmers' }}</p>
                @endif
                @if($reportType === 'loan')
                <p class="mb-0"><strong>Loan Type</strong> &nbsp; {{ $loanType ?: 'All Types' }}</p>
                @endif
                @if($reportType === 'expense')
                <p class="mb-0"><strong>Category</strong> &nbsp; {{ $category ? ucfirst(str_replace('_', ' ', $category)) : 'All Categories' }}</p>
                @endif
            </div>
            @if($reportType === 'loan')
            <div class="col-6">
                <p class="mb-1"><strong>Loan Status</strong> &nbsp; {{ $loanStatus ? ucfirst(str_replace('_', ' ', $loanStatus)) : 'All' }}</p>
                <p class="mb-0"><strong>Payment Status</strong> &nbsp; {{ $paymentStatus ? ucfirst($paymentStatus) : 'All' }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Report Preview -->
<div class="section-card mt-6">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">
            {{ $reportTitle }}
            @if($selectedFarmer && ! in_array($reportType, ['maintenance', 'expense']))
            <span class="text-muted fw-normal">&mdash; {{ $selectedFarmer->full_name }}</span>
            @endif
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
        <table class="table table-hover mb-0 loan-report-table">
            <colgroup>
                <col><col><col><col><col><col><col><col><col><col><col><col>
            </colgroup>
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">No.</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Principal</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Interest</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Penalty</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount Paid</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Remaining Balance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Next Due Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loop->iteration }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-{{ str_pad($row->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->farmer?->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->loanRequest?->purpose ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($row->principal_amount) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($row->interest_charged) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($row->penalty_charged) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($row->total_amount) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($row->amount_paid) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($row->remaining_balance) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->remaining_balance > 0 ? ($row->next_due_date?->format('M d, Y') ?? '—') : '—' }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$row->display_status" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 px-md-6 py-6 text-center text-muted">No loans match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'maintenance')
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
    @elseif($reportType === 'schedule')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Requester</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Size</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->display_name }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$row->member_type === 'member' ? 'Member' : 'Non-Member'" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->machine?->name ?? $row->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->land_size }} ha</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($row->status)" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No schedules match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'cbu')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Category</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->cbu?->farmer?->full_name }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$row->type === 'contribution' ? 'CBU Contribution' : 'CBU Expense'" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->category ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($row->amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ peso($row->balance_after) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No CBU transactions match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'complaint')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Subject</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Manager Response</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->user?->farmer?->full_name ?? $row->user?->name }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->subject }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst(str_replace('_', ' ', $row->status))" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->manager_response ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">No complaints match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @elseif($reportType === 'expense')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Category</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->expense_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ ucfirst(str_replace('_', ' ', $row->category)) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->description }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($row->amount) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($row->status)" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->recordedBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No expenses match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

@if($summary)
<!-- Report Summary + Breakdown -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Summary</h3>
        <table class="table table-sm mb-0">
            <tbody>
                @foreach($summary->rows as $row)
                <tr>
                    <td class="text-muted">{{ $row['label'] }}</td>
                    <td class="text-end fw-semibold {{ ! empty($row['emphasis']) ? 'text-danger' : '' }}">{{ $row['value'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $breakdownTitle }}</h3>
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
</div>

<div class="print-only text-end mt-6">
    <p class="mb-0" style="border-top:1px solid #333; display:inline-block; padding-top:4px; min-width:260px;">&nbsp;</p>
    <p class="mb-0 mt-1">Prepared by:</p>
    <p class="mb-0 fw-bold">{{ auth()->user()->name }}</p>
    <p class="mb-0 text-muted">Centrala Farmers Marketing Cooperative</p>
</div>
@else
<!-- Breakdown -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $breakdownTitle }}</h3>
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
@endif

<style>
    .print-only { display: none; }

    .report-type-card {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.65rem;
        padding: 0.6rem 0.75rem;
        text-decoration: none;
        transition: border-color .15s ease, box-shadow .15s ease;
        min-width: 0;
    }

    .report-type-card:hover {
        border-color: #9ec5fe;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
    }

    .report-type-card.active {
        border-color: var(--bs-primary, #0d6efd);
        box-shadow: 0 0 0 2px rgba(13, 110, 253, .15);
    }

    .report-type-icon {
        width: 34px;
        height: 34px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .report-type-text {
        min-width: 0;
        flex: 1;
    }

    .report-type-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-type-desc {
        font-size: 0.7rem;
        color: #6b7280;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .print-logo-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2f7a4f, #1f5c3a);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .print-filter-box {
        background: #f3f9f5;
        border: 1px solid #d7ead9;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
    }

    /* Comfortable, content-sized column spacing on screen. */
    .loan-report-table th,
    .loan-report-table td {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }

    @media print {
        .no-print, .app-sidebar, .app-topbar { display: none !important; }
        .print-only { display: block; }

        @page { margin: 8mm; }

        /* The loan table has 12 columns — only pin it to fixed percentage
           widths for print, where it must fit the page instead of running
           past the paper edge and getting silently clipped. On screen it
           stays auto-sized (with .table-responsive scroll as a fallback). */
        .table-responsive { overflow: visible !important; }

        .loan-report-table {
            width: 100% !important;
            table-layout: fixed;
            font-size: 8.5px;
        }

        .loan-report-table col:nth-child(1)  { width: 3%; }
        .loan-report-table col:nth-child(2)  { width: 6%; }
        .loan-report-table col:nth-child(3)  { width: 12%; }
        .loan-report-table col:nth-child(4)  { width: 8%; }
        .loan-report-table col:nth-child(5)  { width: 10%; }
        .loan-report-table col:nth-child(6)  { width: 9%; }
        .loan-report-table col:nth-child(7)  { width: 9%; }
        .loan-report-table col:nth-child(8)  { width: 9%; }
        .loan-report-table col:nth-child(9)  { width: 8%; }
        .loan-report-table col:nth-child(10) { width: 9%; }
        .loan-report-table col:nth-child(11) { width: 8%; }
        .loan-report-table col:nth-child(12) { width: 9%; }

        .loan-report-table th,
        .loan-report-table td {
            padding: 3px 5px !important;
            white-space: normal !important;
            word-break: keep-all;
            line-height: 1.15;
            overflow: hidden;
        }

        /* Header words (Principal, Interest, Penalty, Status) have no space
           to wrap at, so they need a smaller type size than the data rows
           to reliably fit their column without spilling into the next one. */
        .loan-report-table thead th {
            font-size: 7.5px;
        }

        .loan-report-table tr {
            page-break-inside: avoid;
        }
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    // Upgrades the "Filter by Member" <select> into a searchable dropdown so
    // managers can find a farmer by typing instead of scrolling a long list.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select.searchable-select').forEach(function (select) {
            if (select.tomselect) {
                return;
            }

            new TomSelect(select, {
                placeholder: select.dataset.placeholder || 'Search...',
                sortField: { field: 'text', direction: 'asc' },
            });
        });
    });
</script>
@endsection
