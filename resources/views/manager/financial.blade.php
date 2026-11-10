@extends('manager.layout')

@section('title', 'Financial Management')
@section('header', 'Financial Management')

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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Operational Expenses" value="{{ peso($stats['operational']) }}" icon="fa-file-invoice" color="secondary" />
    <x-stat-card label="Machinery Expenditures" value="{{ peso($stats['machinery']) }}" icon="fa-tractor" color="warning" />
    <x-stat-card label="Replaceable Parts" value="{{ peso($stats['replaceable_parts']) }}" icon="fa-cogs" color="danger" />
    <x-stat-card label="Total Expenses" value="{{ peso($stats['total']) }}" icon="fa-chart-line" color="primary" />
</div>

<!-- Expense Breakdown -->
<div class="mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Breakdown</h3>
        <div class="space-y-4">
            @php
                $breakdown = [
                    'Operational' => $stats['operational'],
                    'Machinery' => $stats['machinery'],
                    'Replaceable Parts' => $stats['replaceable_parts'],
                ];
                $barColors = ['Operational' => 'bg-secondary', 'Machinery' => 'bg-warning', 'Replaceable Parts' => 'bg-danger'];
            @endphp
            @foreach($breakdown as $label => $amount)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ peso($amount) }}</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar {{ $barColors[$label] }}" style="width: {{ $stats['total'] > 0 ? round(($amount / $stats['total']) * 100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Expense Records Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Expense Records</h3>
        </x-slot:filters>
        <x-slot:actions>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#recordExpenseModal">
                <i class="fas fa-plus"></i><span>Record Expense</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Kind of Expense</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Recorded By</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Proof</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $categoryLabels = ['operational' => 'Operational', 'machinery' => 'Machinery', 'replaceable_parts' => 'Replaceable Parts'];
                @endphp
                @forelse($expenses as $expense)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $expense->expense_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $expense->description }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $categoryLabels[$expense->category] }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($expense->amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $expense->recordedBy?->name ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        @if($expense->proof_url)
                        <a href="{{ $expense->proof_url }}" target="_blank" data-file-viewer data-viewer-title="Proof of Expense — {{ $expense->description }}" class="btn btn-link btn-sm p-0 text-nowrap"><i class="fas fa-paperclip me-1"></i>View</a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No expenses recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Record Expense Modal -->
<div class="modal fade" id="recordExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Record Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.financial.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kind of Expense <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="operational">Operational</option>
                            <option value="machinery">Machinery</option>
                            <option value="replaceable_parts">Replaceable Parts</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Fuel - Tractor #001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (₱) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <x-cbu-proof-input id="recordExpenseProof"
                        label="Proof of Expense"
                        hint="Photo of the receipt or invoice · JPG, PNG or PDF · up to 5 MB"
                        missing-message="Attach the proof of the expense (receipt photo or invoice) before recording." />
                    <p class="text-muted small mb-0">The expense is saved straight to the records. No payment step needed.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
