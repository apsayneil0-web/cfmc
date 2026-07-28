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
            </div>
            <span class="text-sm text-muted">{{ $loans->count() }} approved loan request(s)</span>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Approved Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Approval Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $loan->farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->batch ? $loan->batch->label : 'Regular' }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($loan->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->repayment_terms_months }} months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $loan->updated_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No approved loan requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchFarmerName');

    function applyFilters() {
        const currentUrl = new URL(window.location.href);

        if (searchInput.value) {
            currentUrl.searchParams.set('search', searchInput.value);
        } else {
            currentUrl.searchParams.delete('search');
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
});
</script>
@endsection
