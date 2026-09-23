@extends('admin.layout')

@section('title', 'Activity Logs')
@section('header', 'Activity Logs')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-4 p-md-6 border-b border-gray-200">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 flex-grow-1 flex-wrap">
                <div class="position-relative">
                    <input type="text" id="searchLogs" placeholder="Search description or user" class="form-control ps-5 py-2" style="min-width: 240px;" value="{{ request('search') }}">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select id="filterUser" class="form-select py-2" style="width: auto;">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                <select id="filterAction" class="form-select py-2" style="width: auto;">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
                <input type="date" id="filterDateFrom" class="form-control py-2" style="width: auto;" value="{{ request('date_from') }}" title="From date">
                <input type="date" id="filterDateTo" class="form-control py-2" style="width: auto;" value="{{ request('date_to') }}" title="To date">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date/Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">User</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Action</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted text-nowrap">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-dark">
                        @if($log->user)
                            {{ $log->user->name }}
                            <span class="badge bg-light text-muted border ms-1">{{ $log->user->role_name }}</span>
                        @else
                            <span class="text-muted fst-italic">System / Unknown</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <code class="small">{{ $log->action }}</code>
                    </td>
                    <td class="px-4 px-md-6 py-4 text-dark">{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 px-md-6 py-6 text-center text-muted">No activity recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 px-md-6 py-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <span class="text-muted small">Showing {{ $logs->count() }} of {{ $logs->total() }} entries</span>
        <nav aria-label="Table pagination">
            <ul class="pagination pagination-sm mb-0">
                @if($logs->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                <li class="page-item"><a class="page-link" href="{{ $logs->previousPageUrl() }}">Previous</a></li>
                @endif

                @for($i = 1; $i <= $logs->lastPage(); $i++)
                <li class="page-item {{ $i == $logs->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $logs->url($i) }}">{{ $i }}</a>
                </li>
                @endfor

                @if($logs->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $logs->nextPageUrl() }}">Next</a></li>
                @else
                <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif
            </ul>
        </nav>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchLogs');
    const filterUser = document.getElementById('filterUser');
    const filterAction = document.getElementById('filterAction');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');

    function applyFilters() {
        const currentUrl = new URL(window.location.href);
        const params = { search: searchInput.value, user_id: filterUser.value, action: filterAction.value, date_from: filterDateFrom.value, date_to: filterDateTo.value };

        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                currentUrl.searchParams.set(key, value);
            } else {
                currentUrl.searchParams.delete(key);
            }
        });

        currentUrl.searchParams.delete('page');
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

    filterUser.addEventListener('change', applyFilters);
    filterAction.addEventListener('change', applyFilters);
    filterDateFrom.addEventListener('change', applyFilters);
    filterDateTo.addEventListener('change', applyFilters);
});
</script>
@endsection
