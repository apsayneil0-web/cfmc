@extends('manager.layout')

@section('title', 'Reporting')
@section('header', 'Reporting Module')

@section('content')
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
                    <option value="harvest_payment" @selected($reportType === 'harvest_payment')>Harvest Payment Report</option>
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
                @if($reportType === 'maintenance')
                <label class="form-label fw-semibold">Filter by Machine</label>
                <select name="machine_id" class="form-select">
                    <option value="">All Machines</option>
                    @foreach($machines as $machine)
                    <option value="{{ $machine->id }}" @selected((string) $machineId === (string) $machine->id)>{{ $machine->name }}</option>
                    @endforeach
                </select>
                @else
                <label class="form-label fw-semibold">Filter by Member</label>
                <select name="member_id" id="reportMemberSelect" class="form-select searchable-select" data-placeholder="Search farmer by name..." {{ $reportType === 'expense' ? 'disabled' : '' }}>
                    <option value="">All Members</option>
                    @foreach($farmers as $farmer)
                    <option value="{{ $farmer->id }}" @selected((string) $memberId === (string) $farmer->id)>{{ $farmer->full_name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>

        @if($reportType === 'loan')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
        'harvest_payment' => 'Harvest Payment Report',
    };
@endphp

<!-- Print Letterhead (screen-hidden, shown only when printing/exporting to PDF) -->
<div class="print-only print-letterhead">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="print-logo-circle"><i class="fas fa-seedling"></i></div>
        <div>
            <h2 class="mb-0 fw-bold" style="color: var(--palette-field);">CENTRALA FARMERS MARKETING COOPERATIVE</h2>
            <p class="mb-0 text-muted">Centrala, Surallah, South Cotabato</p>
        </div>
    </div>

    <hr style="border-top: 2px solid var(--palette-field); margin: 1rem 0;">

    <h1 class="text-center fw-bold mb-1">{{ strtoupper($reportTitle) }}</h1>
    <p class="text-center text-muted mb-4">
        <em>Report Generated: {{ now()->format('F j, Y') }}</em> &nbsp;|&nbsp; <em>Generated by: <strong>{{ auth()->user()->name }}</strong></em>
    </p>

    <div class="print-filter-box mb-4">
        <div class="row">
            <div class="col-6">
                <p class="mb-1"><strong>Date Range</strong> &nbsp; From: {{ $dateFrom ?: '—' }} &nbsp; To: {{ $dateTo ?: '—' }}</p>
                @if($reportType === 'maintenance')
                <p class="mb-1"><strong>Machine</strong> &nbsp; {{ $selectedMachine?->name ?? 'All Machines' }}</p>
                @elseif($reportType !== 'expense')
                <p class="mb-1"><strong>Farmer</strong> &nbsp; {{ $selectedFarmer?->full_name ?? 'All Farmers' }}</p>
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
            @if($reportType === 'maintenance' && $selectedMachine)
            <span class="text-muted fw-normal">&mdash; {{ $selectedMachine->name }}</span>
            @elseif($selectedFarmer && ! in_array($reportType, ['maintenance', 'expense']))
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
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Reason</th>
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
    @elseif($reportType === 'harvest_payment')
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Harvest Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Rate</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Payment Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $row->payment_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $row->payer_name }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$row->member_type === 'member' ? 'Member' : 'Non-Member'" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ peso($row->harvest_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ rtrim(rtrim(number_format($row->rate, 2), '0'), '.') }}%</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($row->payment_amount) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No harvest payments match these filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

@if($summary)
<!-- Report Summary + Breakdown -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 summary-breakdown-grid {{ $reportType === 'harvest_payment' ? 'summary-breakdown-grid--print-summary' : '' }}">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 {{ $reportType === 'harvest_payment' ? '' : 'no-print' }}">
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
    <p class="mb-0" style="border-top:1px solid var(--palette-ink); display:inline-block; padding-top:4px; min-width:260px;">&nbsp;</p>
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

    .print-logo-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--palette-field-light), var(--palette-field));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .print-filter-box {
        background: var(--palette-surface-muted);
        border: 1px solid var(--palette-stone);
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

        /* Report Summary is hidden in print for most report types (kept on
           screen) — let the breakdown panel take the full row instead of
           leaving a blank gap where the summary column used to be. The
           Harvest Payment report is the exception: its totals are meant to
           be part of the printed/PDF record, so it keeps both columns. */
        .summary-breakdown-grid {
            grid-template-columns: 1fr;
        }

        .summary-breakdown-grid--print-summary {
            grid-template-columns: 1fr 1fr;
        }

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
