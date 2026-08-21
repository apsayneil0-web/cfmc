@extends('manager.layout')

@section('title', 'Payments')
@section('header', 'Payment Management')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Loan Payments" value="₱{{ number_format($stats['loan_payments'], 2) }}" icon="fa-hand-holding-usd" color="primary" />
    <x-stat-card label="CBU Contributions" value="₱{{ number_format($stats['cbu_contributions'], 2) }}" icon="fa-piggy-bank" color="info" />
    <x-stat-card label="Operational Expenses" value="₱{{ number_format($stats['operational_expenses'], 2) }}" icon="fa-file-invoice" color="warning" />
    <x-stat-card label="Replaceable Parts" value="₱{{ number_format($stats['replaceable_parts'], 2) }}" icon="fa-cogs" color="danger" />
</div>

<!-- Payments Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search payments..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Types</option>
                <option>Loan Payment</option>
                <option>CBU Contribution</option>
                <option>Operational Expense</option>
                <option>Replaceable Parts</option>
            </select>
            <input type="date" class="form-control" style="width: auto;">
        </x-slot:filters>
        <x-slot:actions>
            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#recordCbuPaymentModal">
                <i class="fas fa-piggy-bank"></i><span>Record CBU Payment</span>
            </button>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                <i class="fas fa-plus"></i><span>Record Loan Payment</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Transaction ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Payor/Payer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Payment Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Reference No.</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $payment->transaction_code }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $payment->date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $payment->payer }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$payment->type_label" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $payment->reference }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($payment->amount) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Completed" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewPaymentModal{{ $payment->kind }}{{ $payment->id }}" />
                            @if($payment->kind === 'loan')
                            <a href="{{ route('manager.payment.receipt', $payment->id) }}" target="_blank" class="icon-btn text-secondary" title="Receipt"><i class="fas fa-receipt"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>

                <x-modal id="viewPaymentModal{{ $payment->kind }}{{ $payment->id }}" title="Transaction Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Transaction ID</label><p class="fw-medium mb-0">{{ $payment->transaction_code }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">{{ $payment->kind === 'loan' ? 'Loan' : 'Farmer' }}</label><p class="fw-medium mb-0">{{ $payment->reference }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Payer</label><p class="fw-medium mb-0">{{ $payment->payer }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $payment->date->format('M d, Y') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Amount</label><p class="fw-medium mb-0">{{ peso($payment->amount) }}</p></div>
                        @if($payment->balance_after !== null)
                        <div class="col-6"><label class="text-muted small d-block">Balance After</label><p class="fw-medium mb-0">{{ peso($payment->balance_after) }}</p></div>
                        @endif
                        @if($payment->notes)
                        <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="fw-medium mb-0">{{ $payment->notes }}</p></div>
                        @endif
                    </div>
                    @if($payment->kind === 'loan')
                    <div class="mt-3 text-end">
                        <a href="{{ route('manager.payment.receipt', $payment->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-receipt me-1"></i> View Receipt
                        </a>
                    </div>
                    @endif
                </x-modal>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No payments recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 45 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Auto-Update Info -->
<x-info-banner variant="success" title="Automatic Updates" class="mt-6">
    Payments automatically update loan balance, CBU balance, and financial records.
</x-info-banner>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Record Loan Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.payment.record') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Loan <span class="text-danger">*</span></label>
                        <select name="loan_id" id="recordPaymentLoanSelect" class="form-select" required>
                            <option value="" disabled selected>Select a loan</option>
                            @forelse($payableLoans as $loan)
                            <option value="{{ $loan->id }}" data-balance="{{ $loan->remaining_balance }}">
                                LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }} — {{ $loan->farmer->full_name }} (Balance: {{ peso($loan->remaining_balance) }})
                            </option>
                            @empty
                            <option value="" disabled>No active loans</option>
                            @endforelse
                        </select>
                    </div>
                    <p class="text-muted small" id="recordPaymentBalanceHint"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="recordPaymentAmount" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record CBU Payment Modal -->
<div class="modal fade" id="recordCbuPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-piggy-bank me-2"></i>Record CBU Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.payment.record-cbu') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                        <select name="farmer_id" id="recordCbuFarmerSelect" class="form-select" required>
                            <option value="" disabled selected>Select a farmer</option>
                            @forelse($cbuFarmers as $farmer)
                            <option value="{{ $farmer->id }}" data-balance="{{ $farmer->cbu->balance ?? 0 }}">
                                FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }} — {{ $farmer->full_name }} (CBU Balance: {{ peso($farmer->cbu->balance ?? 0) }})
                            </option>
                            @empty
                            <option value="" disabled>No approved farmers</option>
                            @endforelse
                        </select>
                    </div>
                    <p class="text-muted small" id="recordCbuBalanceHint"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Entry Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="contribution">Contribution</option>
                            <option value="expense">Expense (charged against CBU balance)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Monthly Contribution, Share Capital">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('recordPaymentLoanSelect')?.addEventListener('change', function () {
        var option = this.options[this.selectedIndex];
        var balance = option ? option.getAttribute('data-balance') : null;
        var amountInput = document.getElementById('recordPaymentAmount');
        var hint = document.getElementById('recordPaymentBalanceHint');

        if (balance) {
            amountInput.max = balance;
            hint.textContent = 'Current balance: ₱' + parseFloat(balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            amountInput.removeAttribute('max');
            hint.textContent = '';
        }
    });

    document.getElementById('recordCbuFarmerSelect')?.addEventListener('change', function () {
        var option = this.options[this.selectedIndex];
        var balance = option ? option.getAttribute('data-balance') : null;
        var hint = document.getElementById('recordCbuBalanceHint');

        hint.textContent = balance
            ? 'Current CBU balance: ₱' + parseFloat(balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '';
    });
</script>
@endsection
