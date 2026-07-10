@extends('admin.layout')

@section('title', 'Member Information')
@section('header', 'Member Information')

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
                <select id="filterStatus" class="form-select py-2" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Barangay</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Contact Number</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Crop</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Home Address</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Area</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $member->id }}</td>
                    <td class="px-4 px-md-6 py-4 text-dark">{{ $member->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $member->barangay ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $member->contact_number }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $member->crop->name ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $member->municipality }}, {{ $member->province }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $member->land_area }} ha</td>
                    <td class="px-4 px-md-6 py-4">
                        <x-status-badge :status="$member->status == 'archived' ? 'Inactive' : 'Active'" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No member records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchFarmerName');
    const filterStatus = document.getElementById('filterStatus');

    function applyFilters() {
        const currentUrl = new URL(window.location.href);

        if (searchInput.value) {
            currentUrl.searchParams.set('search', searchInput.value);
        } else {
            currentUrl.searchParams.delete('search');
        }

        if (filterStatus.value) {
            currentUrl.searchParams.set('status', filterStatus.value);
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

    filterStatus.addEventListener('change', applyFilters);
});
</script>
@endsection
