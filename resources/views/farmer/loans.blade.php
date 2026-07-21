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
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Remaining Balance" value="{{ peso($activeLoan->remaining_balance) }}" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Monthly Due" value="{{ peso($activeLoan->monthly_due) }}" icon="fa-calendar-day" color="warning" />
    <x-stat-card label="Next Due Date" value="{{ $activeLoan->next_due_date->format('M d, Y') }}" icon="fa-clock" color="primary" />
</div>

<div class="section-card mb-6">
    <div class="p-4 p-md-6 border-b border-gray-200 d-flex align-items-center justify-content-between">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Active Loan</h3>
        <x-status-badge :status="$activeLoan->status === 'fully_paid' ? 'Fully Paid' : ucfirst($activeLoan->status)" />
    </div>
    <div class="p-4 p-md-6 row g-3">
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">{{ peso($activeLoan->principal_amount) }}</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Interest Rate</label><p class="fw-medium mb-0">{{ $activeLoan->interest_rate }}% per due date</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $activeLoan->repayment_terms_months }} months</p></div>
        <div class="col-6 col-md-3"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $activeLoan->collateral ?? '—' }}</p></div>
    </div>

    <h4 class="text-sm fw-semibold text-dark px-4 px-md-6 mb-2">Payment &amp; Interest History</h4>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 small">Date</th>
                    <th class="small">Type</th>
                    <th class="small">Amount</th>
                    <th class="small">Balance After</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeLoan->payments->sortByDesc('transaction_date') as $payment)
                <tr>
                    <td class="px-4 px-md-6 small">{{ $payment->transaction_date->format('M d, Y') }}</td>
                    <td class="small">
                        @if($payment->type === 'payment')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                        @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Interest</span>
                        @endif
                    </td>
                    <td class="small">{{ $payment->type === 'payment' ? '-' : '+' }}{{ peso($payment->amount) }}</td>
                    <td class="small">{{ peso($payment->balance_after) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted small py-3">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Loan Applications -->
<div class="section-card">
    <div class="p-4 p-md-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Loan Applications</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
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
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">
                        {{ $req->type === 'batch' ? ($req->batch?->label ?? 'Batch') : 'Regular Loan' }}
                    </td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($req->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->purpose ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4">
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
@endsection
