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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <x-stat-card label="Loan Payments" value="₱{{ number_format($stats['loan_payments'], 2) }}" icon="fa-hand-holding-usd" color="primary" />
    <x-stat-card label="CBU Contributions" value="₱{{ number_format($stats['cbu_contributions'], 2) }}" icon="fa-piggy-bank" color="info" />
    <x-stat-card label="Harvest Payments" value="₱{{ number_format($stats['harvest_payments'], 2) }}" icon="fa-seedling" color="success" />
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
            <select id="paymentTypeFilter" class="form-select" style="width: auto;">
                <option value="">All Types</option>
                <option value="Loan Payment">Loan Payment</option>
                <option value="CBU Contribution">CBU Contribution</option>
                <option value="Harvest Payment">Harvest Payment</option>
                <option value="Operational Expense">Operational Expense</option>
                <option value="Replaceable Parts">Replaceable Parts</option>
            </select>
            <input type="date" class="form-control" style="width: auto;">
        </x-slot:filters>
        <x-slot:actions>
            <div class="dropdown">
                <button type="button" class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus"></i><span>Record Payment</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="fas fa-hand-holding-usd text-primary"></i>Loan Payment
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#recordCbuPaymentModal">
                            <i class="fas fa-piggy-bank text-primary"></i>CBU Payment
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#payExpenseModal">
                            <i class="fas fa-file-invoice text-warning"></i>Expense Payment
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#recordHarvestPaymentModal">
                            <i class="fas fa-seedling text-success"></i>Harvesting Payment
                        </a>
                    </li>
                </ul>
            </div>
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
            <tbody id="paymentsTableBody">
                @forelse($payments as $payment)
                <tr data-filter-category="{{ $payment->filter_category }}">
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $payment->transaction_code }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $payment->date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $payment->payer }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$payment->type_label" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $payment->reference }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($payment->amount) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$payment->status_label" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewPaymentModal{{ $payment->kind }}{{ $payment->id }}" />
                            @if($payment->kind === 'loan')
                            <a href="{{ route('manager.payment.receipt', $payment->id) }}" target="_blank" class="icon-btn text-secondary" title="Receipt"><i class="fas fa-receipt"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No payments recorded yet.</td>
                </tr>
                @endforelse
                <tr id="paymentsNoFilterMatch" class="d-none">
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No payments match this type.</td>
                </tr>
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

{{-- Modals live outside the table: a <div> can't be a direct child of <tbody>. --}}
@foreach($payments as $payment)
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
@endforeach

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
                        <select name="loan_id" id="recordPaymentLoanSelect" class="form-select searchable-select" data-placeholder="Search farmer by name..." required>
                            <option value="" disabled selected>Select a loan</option>
                            @forelse($payableLoans as $loan)
                            <option value="{{ $loan->id }}" data-balance="{{ $loan->remaining_balance }}" data-monthly-due="{{ $loan->amount_due }}" data-next-due-date="{{ $loan->next_due_date?->format('M d, Y') }}">
                                LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }} — {{ $loan->farmer->full_name }} (Balance: {{ peso($loan->remaining_balance) }})
                            </option>
                            @empty
                            <option value="" disabled>No active loans</option>
                            @endforelse
                        </select>
                    </div>
                    <p class="text-muted small" id="recordPaymentBalanceHint"></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Type <span class="text-danger">*</span></label>
                        <select name="type" id="recordPaymentTypeSelect" class="form-select" required>
                            <option value="payment" selected>Regular Payment</option>
                            <option value="prepayment">Prepayment (ahead of the due date)</option>
                            <option value="partial">Partial Payment</option>
                        </select>
                    </div>
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
                        <select name="farmer_id" id="recordCbuFarmerSelect" class="form-select searchable-select" data-placeholder="Search farmer by name..." required>
                            <option value="" disabled selected>Select a farmer</option>
                            @forelse($cbuFarmers as $farmer)
                            @php $hasContributed = $farmer->cbu?->transactions->where('type', 'contribution')->isNotEmpty() ?? false; @endphp
                            <option value="{{ $farmer->id }}" data-balance="{{ $farmer->cbu->balance ?? 0 }}" data-has-contributed="{{ $hasContributed ? '1' : '0' }}" data-first-minimum="{{ $farmer->first_cbu_contribution_minimum }}">
                                FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }} — {{ $farmer->full_name }} (CBU Balance: {{ peso($farmer->cbu->balance ?? 0) }})
                            </option>
                            @empty
                            <option value="" disabled>No approved farmers</option>
                            @endforelse
                        </select>
                    </div>
                    <p class="text-muted small" id="recordCbuBalanceHint"></p>
                    <input type="hidden" name="type" value="contribution">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="recordCbuAmountInput" class="form-control" required>
                        <p class="text-muted small mb-0 mt-1" id="recordCbuMinimumHint"></p>
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

<!-- Record Expense Payment Modal -->
<div class="modal fade" id="payExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Record Expense Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.payment.pay-expense') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pending Expense <span class="text-danger">*</span></label>
                        <select name="expense_id" id="payExpenseSelect" class="form-select searchable-select" data-placeholder="Search expenses..." required>
                            <option value="" disabled selected>Select an expense</option>
                            @forelse($payableExpenses as $expense)
                            <option value="{{ $expense->id }}">
                                EXP-{{ str_pad($expense->id, 3, '0', STR_PAD_LEFT) }} — {{ $expense->description }} ({{ peso($expense->amount) }})
                            </option>
                            @empty
                            <option value="" disabled>No pending expenses</option>
                            @endforelse
                        </select>
                        <p class="text-muted small mb-0 mt-1">Marks the expense as paid, dated today.</p>
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

<!-- Record Harvesting Payment Modal -->
<div class="modal fade" id="recordHarvestPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-seedling me-2"></i>Record Harvesting Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.payment.record-harvest') }}" method="POST" id="recordHarvestForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Farmer Type <span class="text-danger">*</span></label>
                        <select name="member_type" id="harvestMemberType" class="form-select" required>
                            <option value="member" selected>Member ({{ rtrim(rtrim(number_format(\App\Models\HarvestPayment::MEMBER_RATE, 2), '0'), '.') }}%)</option>
                            <option value="non-member">Non-member ({{ rtrim(rtrim(number_format(\App\Models\HarvestPayment::NON_MEMBER_RATE, 2), '0'), '.') }}%)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="harvestFarmerSelectGroup">
                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                        <select name="farmer_id" id="harvestFarmerSelect" class="form-select searchable-select" data-placeholder="Search farmer by name..." required>
                            <option value="" disabled selected>Select a farmer</option>
                            @forelse($cbuFarmers as $farmer)
                            <option value="{{ $farmer->id }}">FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }} — {{ $farmer->full_name }}</option>
                            @empty
                            <option value="" disabled>No approved farmers</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="harvestFarmerNameGroup">
                        <label class="form-label fw-semibold">Farmer Name <span class="text-danger">*</span></label>
                        <input type="text" name="farmer_name" id="harvestFarmerNameInput" class="form-control" placeholder="Full name" list="nonMemberNamesList" autocomplete="off">
                        <datalist id="nonMemberNamesList">
                            @foreach($nonMemberNames as $name)
                            <option value="{{ $name }}"></option>
                            @endforeach
                        </datalist>
                        <p class="text-muted small mb-0 mt-1">Start typing to see returning non-members, or enter a new name.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Total Harvest Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="harvest_amount" id="harvestAmountInput" class="form-control" required>
                    </div>
                    <div class="text-center mb-3">
                        <button type="button" id="harvestCalculateBtn" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-calculator me-1"></i>Calculate
                        </button>
                    </div>
                    <div id="harvestPreview" class="bg-light rounded p-3 mb-3 d-none">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Harvest Amount</span>
                            <span id="harvestPreviewAmount" class="fw-medium"></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Applicable Rate</span>
                            <span id="harvestPreviewRate" class="fw-medium"></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Cooperative Payment Due</span>
                            <span id="harvestPreviewPayment" class="fw-bold text-success"></span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm &amp; Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    // Upgrades the Loan and Farmer <select> fields in the payment modals into
    // searchable dropdowns once their modal is shown. Tom Select keeps the
    // underlying <select> in sync and still fires native "change" events on
    // it, so the balance/due-date hint listeners below need no changes.
    document.addEventListener('shown.bs.modal', function (event) {
        event.target.querySelectorAll('select.searchable-select').forEach(function (select) {
            if (select.tomselect) {
                return;
            }

            new TomSelect(select, {
                placeholder: select.dataset.placeholder || select.options[0]?.textContent.trim() || 'Search...',
                sortField: { field: 'text', direction: 'asc' },
            });
        });
    });

    document.getElementById('recordPaymentLoanSelect')?.addEventListener('change', function () {
        var option = this.options[this.selectedIndex];
        var balance = option ? option.getAttribute('data-balance') : null;
        var monthlyDue = option ? option.getAttribute('data-monthly-due') : null;
        var nextDueDate = option ? option.getAttribute('data-next-due-date') : null;
        var amountInput = document.getElementById('recordPaymentAmount');
        var hint = document.getElementById('recordPaymentBalanceHint');

        if (balance) {
            amountInput.max = balance;
            var text = 'Current balance: ₱' + parseFloat(balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (monthlyDue) {
                text += ' &bull; Monthly due: ₱' + parseFloat(monthlyDue).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (nextDueDate) {
                text += ' (due ' + nextDueDate + ')';
            }
            hint.innerHTML = text;
        } else {
            amountInput.removeAttribute('max');
            hint.textContent = '';
        }
    });

    // Every entry recorded here is a contribution: a farmer's very first one
    // must meet their land-area-based minimum (₱4,000-₱12,000); every one
    // after that only needs to clear a flat ₱1,000.
    function updateCbuMinimumHint() {
        var farmerSelect = document.getElementById('recordCbuFarmerSelect');
        var amountInput = document.getElementById('recordCbuAmountInput');
        var hint = document.getElementById('recordCbuMinimumHint');
        var option = farmerSelect.options[farmerSelect.selectedIndex];

        if (!option || !option.value) {
            amountInput.removeAttribute('min');
            hint.textContent = '';
            return;
        }

        var hasContributed = option.getAttribute('data-has-contributed') === '1';
        var minimum = hasContributed ? 1000 : parseFloat(option.getAttribute('data-first-minimum'));

        amountInput.min = minimum;
        hint.textContent = hasContributed
            ? 'Minimum contribution: ₱' + minimum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : 'First contribution minimum: ₱' + minimum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.getElementById('recordCbuFarmerSelect')?.addEventListener('change', function () {
        var option = this.options[this.selectedIndex];
        var balance = option ? option.getAttribute('data-balance') : null;
        var hint = document.getElementById('recordCbuBalanceHint');

        hint.textContent = balance
            ? 'Current CBU balance: ₱' + parseFloat(balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '';

        updateCbuMinimumHint();
    });

    // Harvesting Payment: toggles between an existing member (dropdown) and
    // a manually-typed non-member name, and previews the 9%/12% calculation
    // client-side. The server always recomputes the rate/amount itself —
    // this preview is only for the Manager's confirmation, never trusted as-is.
    var HARVEST_MEMBER_RATE = {{ \App\Models\HarvestPayment::MEMBER_RATE }};
    var HARVEST_NON_MEMBER_RATE = {{ \App\Models\HarvestPayment::NON_MEMBER_RATE }};

    function formatPeso(amount) {
        return '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function toggleHarvestFarmerFields() {
        var memberType = document.getElementById('harvestMemberType').value;
        var selectGroup = document.getElementById('harvestFarmerSelectGroup');
        var nameGroup = document.getElementById('harvestFarmerNameGroup');
        var farmerSelect = document.getElementById('harvestFarmerSelect');
        var nameInput = document.getElementById('harvestFarmerNameInput');

        if (memberType === 'member') {
            selectGroup.classList.remove('d-none');
            nameGroup.classList.add('d-none');
            farmerSelect.setAttribute('required', 'required');
            nameInput.removeAttribute('required');
            nameInput.value = '';
        } else {
            selectGroup.classList.add('d-none');
            nameGroup.classList.remove('d-none');
            farmerSelect.removeAttribute('required');
            nameInput.setAttribute('required', 'required');
        }
    }

    function updateHarvestPreview() {
        var memberType = document.getElementById('harvestMemberType').value;
        var amount = parseFloat(document.getElementById('harvestAmountInput').value);
        var preview = document.getElementById('harvestPreview');

        if (!amount || amount <= 0) {
            preview.classList.add('d-none');
            return;
        }

        var rate = memberType === 'member' ? HARVEST_MEMBER_RATE : HARVEST_NON_MEMBER_RATE;
        var payment = Math.round(amount * rate) / 100;

        document.getElementById('harvestPreviewAmount').textContent = formatPeso(amount);
        document.getElementById('harvestPreviewRate').textContent = rate + '%';
        document.getElementById('harvestPreviewPayment').textContent = formatPeso(payment);
        preview.classList.remove('d-none');
    }

    document.getElementById('harvestMemberType')?.addEventListener('change', function () {
        toggleHarvestFarmerFields();
        updateHarvestPreview();
    });
    document.getElementById('harvestAmountInput')?.addEventListener('input', updateHarvestPreview);
    document.getElementById('harvestCalculateBtn')?.addEventListener('click', updateHarvestPreview);

    // "All Types" filter: purely client-side, since the whole feed is
    // already rendered — toggles row visibility by the category each row
    // was tagged with server-side (data-filter-category).
    document.getElementById('paymentTypeFilter')?.addEventListener('change', function () {
        var selected = this.value;
        var rows = document.querySelectorAll('#paymentsTableBody tr[data-filter-category]');
        var visibleCount = 0;

        rows.forEach(function (row) {
            var matches = !selected || row.getAttribute('data-filter-category') === selected;
            row.classList.toggle('d-none', !matches);
            if (matches) {
                visibleCount++;
            }
        });

        var noMatchRow = document.getElementById('paymentsNoFilterMatch');
        if (noMatchRow) {
            noMatchRow.classList.toggle('d-none', visibleCount !== 0 || rows.length === 0);
        }
    });
</script>
@endsection
