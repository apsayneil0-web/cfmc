@extends('farmer.layout')

@section('title', 'My Loans')
@section('header', 'My Loans')

@section('content')
@if(!$farmer)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Your account isn't linked to a membership record yet, so there's no loan history to show.
</div>
@else

<!-- Active Loan Summary -->
@if($activeLoan)
@if($activeLoan->status === 'pending_disbursement')
<x-info-banner variant="info" title="Loan Approved" class="mb-6">
    Your loan terms have been finalized and are awaiting release of funds. Repayment tracking will begin once the amount is disbursed to you.
</x-info-banner>
@else
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Remaining Balance" value="{{ peso($activeLoan->remaining_balance) }}" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Monthly Due" value="{{ peso($activeLoan->monthly_due) }}" icon="fa-calendar-day" color="warning" />
    <x-stat-card label="Next Due Date" value="{{ $activeLoan->next_due_date->format('M d, Y') }}" icon="fa-clock" color="primary" />
</div>
@endif

<div class="section-card mb-6">
    <div class="p-4 p-md-6 border-b border-gray-200 d-flex align-items-center justify-content-between">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Active Loan</h3>
        <x-status-badge :status="ucwords(str_replace('_', ' ', $activeLoan->status))" />
    </div>
    <div class="p-4 p-md-6 row g-3">
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">{{ peso($activeLoan->principal_amount) }}</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Interest Rate</label><p class="fw-medium mb-0">{{ $activeLoan->interest_rate }}% per due date</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $activeLoan->repayment_terms_months }} months{{ $activeLoan->effective_term_months > $activeLoan->repayment_terms_months ? ' — extended, now on month '.$activeLoan->current_installment_number : '' }}</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $activeLoan->collateral ?? '—' }}</p></div>
    </div>

    @if(!empty($activeLoan->installment_schedule))
    <h4 class="text-sm fw-semibold text-dark px-4 px-md-6 mb-3">Repayment Schedule</h4>
    <div class="px-4 px-md-6 mb-4">
        <div class="loan-timeline">
            @foreach($activeLoan->installment_schedule as $row)
            <div class="timeline-row">
                <div class="timeline-marker timeline-marker-{{ $row->status }}">
                    @if($row->status === 'paid')
                    <i class="fas fa-check"></i>
                    @endif
                </div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold text-dark">
                                {{ $row->due_date->format('M d, Y') }}
                                @if($row->status === 'late')
                                <span class="badge bg-danger ms-1">LATE</span>
                                @endif
                            </div>
                            <div class="small text-muted">{{ $row->number }} of {{ $activeLoan->effective_term_months }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold {{ $row->status === 'paid' ? 'text-muted text-decoration-line-through' : 'text-primary' }}">{{ peso($row->amount) }}</div>
                            <button type="button" class="btn btn-sm btn-link p-0 text-muted text-decoration-none" data-bs-toggle="collapse" data-bs-target="#installmentDetail{{ $activeLoan->id }}-{{ $row->number }}" aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="collapse small text-muted mt-1" id="installmentDetail{{ $activeLoan->id }}-{{ $row->number }}">
                        Principal: {{ peso($activeLoan->installment_amount) }} &bull; Interest: {{ peso($activeLoan->monthly_due - $activeLoan->installment_amount) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-muted small mb-0 mt-2"><i class="fas fa-info-circle me-1"></i> This is a projected schedule based on your current due date. Payments are recorded by your CFMC Manager &mdash; visit or contact the cooperative office to make a payment.</p>
    </div>
    @endif

    <h4 class="text-sm fw-semibold text-dark px-4 px-md-6 mb-2">Payment &amp; Interest History</h4>
    <div class="table-responsive">
        <table class="table table-sm mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 small">Date</th>
                    <th class="small">Type</th>
                    <th class="small">Amount</th>
                    <th class="small">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeLoan->payments->sortBy('created_at') as $payment)
                <tr>
                    <td class="px-4 px-md-6 small" data-label="Date">{{ $payment->transaction_date->format('M d, Y') }}</td>
                    <td class="small" data-label="Type">
                        @if($payment->type === 'payment')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                        @elseif($payment->type === 'prepayment')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Prepayment</span>
                        @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Interest</span>
                        @endif
                    </td>
                    <td class="small" data-label="Amount">{{ $payment->type === 'interest' ? '+' : '-' }}{{ peso($payment->amount) }}</td>
                    <td class="small" data-label="Balance After">{{ peso($payment->balance_after) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted small py-3">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Loan History (fully paid loans) -->
@if($paidLoans->isNotEmpty())
<div class="section-card mb-6">
    <div class="p-4 p-md-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Loan History</h3>
        <span class="text-sm text-muted">Loans you've fully paid off</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Principal</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Disbursed</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Paid Off</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Paid</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paidLoans as $loan)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark" data-label="Loan ID">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Principal">{{ peso($loan->principal_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Disbursed">{{ $loan->disbursed_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Paid Off">{{ $loan->payments->max('transaction_date')?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark" data-label="Total Paid">{{ peso($loan->payments->whereIn('type', ['payment', 'prepayment'])->sum('amount')) }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Status"><x-status-badge status="Fully Paid" /></td>
                    <td class="px-4 px-md-6 py-4" data-label="Actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paidLoanModal{{ $loan->id }}">
                            <i class="fas fa-eye me-1"></i> View
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@foreach($paidLoans as $loan)
<x-modal id="paidLoanModal{{ $loan->id }}" title="LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }} — Fully Paid">
    <div class="row g-3 mb-3">
        <div class="col-6"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">{{ peso($loan->principal_amount) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Interest Rate</label><p class="fw-medium mb-0">{{ $loan->interest_rate }}% per due date</p></div>
        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $loan->repayment_terms_months }} months</p></div>
        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $loan->collateral ?? '—' }}</p></div>
    </div>

    <h4 class="text-sm fw-semibold text-dark mb-2">Payment &amp; Interest History</h4>
    <div class="table-responsive" style="max-height: 300px;">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Date</th>
                    <th class="small">Type</th>
                    <th class="small">Amount</th>
                    <th class="small">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loan->payments->sortBy('created_at') as $payment)
                <tr>
                    <td class="small">{{ $payment->transaction_date->format('M d, Y') }}</td>
                    <td class="small">
                        @if($payment->type === 'payment')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                        @elseif($payment->type === 'prepayment')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Prepayment</span>
                        @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Interest</span>
                        @endif
                    </td>
                    <td class="small">{{ $payment->type === 'interest' ? '+' : '-' }}{{ peso($payment->amount) }}</td>
                    <td class="small">{{ peso($payment->balance_after) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted small py-3">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-modal>
@endforeach
@endif

<!-- Loan Applications -->
<div class="section-card">
    <div class="p-4 p-md-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Loan Applications</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date Requested</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount Requested</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($loanRequests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Date Requested">{{ $req->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Type">
                        {{ $req->type === 'batch' ? ($req->batch?->label ?? 'Batch') : 'Regular Loan' }}
                    </td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark" data-label="Amount Requested">{{ peso($req->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Purpose">{{ $req->purpose ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Status">
                        <x-status-badge :status="ucfirst($req->status)" />
                        @if($req->status === 'denied' && $req->denial_reason)
                        <p class="text-danger small mb-0 mt-1">{{ $req->denial_reason }}</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">No loan applications yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<style>
    .loan-timeline {
        position: relative;
        padding-left: 28px;
    }
    .timeline-row {
        position: relative;
        padding-bottom: 22px;
    }
    .timeline-row:last-child {
        padding-bottom: 0;
    }
    .timeline-row:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 16px;
        bottom: -6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-marker {
        position: absolute;
        left: -28px;
        top: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
        color: #fff;
    }
    .timeline-marker-paid {
        background: #16a34a;
        border-color: #16a34a;
    }
    .timeline-marker-late {
        background: #dc2626;
        border-color: #dc2626;
    }
    .timeline-marker-current {
        background: #2563eb;
        border-color: #2563eb;
    }
    .timeline-marker-upcoming {
        background: #fff;
        border-color: #cbd5e1;
    }
</style>
@endsection
