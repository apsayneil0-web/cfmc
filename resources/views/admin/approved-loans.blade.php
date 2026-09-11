@extends('admin.layout')

@section('title', 'Approved Loans')
@section('header', 'Approved Loans')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-4 p-md-6 border-b border-gray-200">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 flex-grow-1 flex-wrap">
                <div class="position-relative">
                    <input type="text" id="searchFarmerName" placeholder="Search farmer name" class="form-control ps-5 py-2" style="min-width: 260px;" value="{{ request('search') }}">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select id="statusFilter" class="form-select" style="width: auto;">
                    <option value="" {{ request('status') !== 'archived' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <span class="text-sm text-muted">{{ $loans->count() + $batchGroups->sum(fn($b) => $b->loanRequests->count()) }} approved loan request(s)</span>
        </div>
    </div>

    <!-- Approved Batch Loans (folded to one row per batch) -->
    @if($batchGroups->isNotEmpty())
    <div class="p-4 p-md-6 border-bottom">
        <h3 class="text-base fw-semibold text-gray-900 mb-0">Approved Batch Loans</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Members</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Approved</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Approval Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchGroups as $batch)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $batch->label }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $batch->loanRequests->count() }} farmer(s)</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($batch->loanRequests->sum('requested_amount')) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $batch->loanRequests->max('updated_at')?->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="request('status') === 'archived' ? 'Archived' : 'Approved'" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#approvedBatchModal{{ $batch->id }}">
                                <i class="fas fa-eye me-1"></i> View
                            </button>
                            @if(request('status') === 'archived')
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#unarchiveBatchModal{{ $batch->id }}">
                                <i class="fas fa-box-open me-1"></i> Unarchive
                            </button>
                            @else
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#archiveBatchModal{{ $batch->id }}">
                                <i class="fas fa-archive me-1"></i> Archive
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @foreach($batchGroups as $batch)
    <!-- Archive/Unarchive Confirmation Modal -->
    <div class="modal fade" id="{{ request('status') === 'archived' ? 'unarchiveBatchModal' : 'archiveBatchModal' }}{{ $batch->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header {{ request('status') === 'archived' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                    <h5 class="modal-title fw-bold">
                        <i class="fas {{ request('status') === 'archived' ? 'fa-box-open' : 'fa-archive' }} me-2"></i>
                        {{ request('status') === 'archived' ? 'Restore' : 'Archive' }} {{ $batch->label }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        @if(request('status') === 'archived')
                        Restore all {{ $batch->loanRequests->count() }} archived member(s) of {{ $batch->label }} back into the active list?
                        @else
                        Archive all {{ $batch->loanRequests->count() }} approved member(s) of {{ $batch->label }}? This removes them from the active list but keeps their approval on record.
                        @endif
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route(request('status') === 'archived' ? 'admin.approved-loans.batch-unarchive' : 'admin.approved-loans.batch-archive', $batch) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ request('status') === 'archived' ? 'btn-success' : 'btn-secondary' }}">
                            {{ request('status') === 'archived' ? 'Restore' : 'Archive' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="approvedBatchModal{{ $batch->id }}" title="{{ $batch->label }} — Approved Members">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small">Farmer</th>
                        <th class="small">Amount</th>
                        <th class="small">Purpose</th>
                        <th class="small">Terms</th>
                        <th class="small">Approval Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batch->loanRequests as $member)
                    <tr>
                        <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                        <td class="small">{{ peso($member->requested_amount) }}</td>
                        <td class="small text-muted">{{ $member->purpose }}</td>
                        <td class="small text-muted">{{ $member->repayment_terms_months }} months</td>
                        <td class="small text-muted">{{ $member->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-modal>
    @endforeach
    @endif

    <!-- Approved Regular Loans -->
    <div class="p-4 p-md-6 border-bottom">
        <h3 class="text-base fw-semibold text-gray-900 mb-0">Approved Regular Loans</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Approved Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Approval Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $loan->farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($loan->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->repayment_terms_months }} months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->updated_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$loan->archived_at ? 'Archived' : 'Approved'" /></td>
                    <td class="px-4 px-md-6 py-4">
                        @if($loan->archived_at)
                        <x-icon-button icon="fa-box-open" color="success" title="Unarchive" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $loan->id }}" />
                        @else
                        <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $loan->id }}" />
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No approved regular loan requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modals rendered outside the table: a <div> is not valid directly
         inside a table body, and browsers "correct" that by ejecting
         everything after the first row's modal out of the table, breaking
         every row after the first. --}}
    @foreach($loans as $loan)
    <!-- Archive/Unarchive Confirmation Modal -->
    <div class="modal fade" id="{{ $loan->archived_at ? 'unarchiveModal' : 'archiveModal' }}{{ $loan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header {{ $loan->archived_at ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                    <h5 class="modal-title fw-bold">
                        <i class="fas {{ $loan->archived_at ? 'fa-box-open' : 'fa-archive' }} me-2"></i>
                        {{ $loan->archived_at ? 'Restore' : 'Archive' }} Loan Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        @if($loan->archived_at)
                        Restore {{ $loan->farmer->full_name }}'s approved loan request back into the active list?
                        @else
                        Archive {{ $loan->farmer->full_name }}'s approved loan request? This removes it from the active list but keeps the approval on record.
                        @endif
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route($loan->archived_at ? 'admin.approved-loans.unarchive' : 'admin.approved-loans.archive', $loan) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ $loan->archived_at ? 'btn-success' : 'btn-secondary' }}">
                            {{ $loan->archived_at ? 'Restore' : 'Archive' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchFarmerName');
    const statusFilter = document.getElementById('statusFilter');

    function applyFilters() {
        const currentUrl = new URL(window.location.href);

        if (searchInput.value) {
            currentUrl.searchParams.set('search', searchInput.value);
        } else {
            currentUrl.searchParams.delete('search');
        }

        if (statusFilter.value) {
            currentUrl.searchParams.set('status', statusFilter.value);
        } else {
            currentUrl.searchParams.delete('status');
        }

        window.location.href = currentUrl.toString();
    }

    let searchTimeout;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    searchInput.addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilters();
        }
    });

    statusFilter.addEventListener('change', applyFilters);
});
</script>
@endsection
