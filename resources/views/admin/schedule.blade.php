@extends('admin.layout')

@section('title', 'View Schedule')
@section('header', 'Machinery Schedule Overview')

@section('content')
<div class="section-card">
    <div class="table-toolbar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <form method="GET" action="{{ route('admin.schedule') }}" id="scheduleFilterForm" class="d-flex align-items-center gap-3 flex-wrap">
            <div class="position-relative">
                <input type="text" name="search" id="scheduleSearchInput" value="{{ request('search') }}" placeholder="Search farmer, machinery or location..." class="form-control ps-5 pe-5 py-2" style="min-width: 260px;" autocomplete="off">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" id="scheduleSearchIcon" style="font-size: 14px;"></i>
                <button type="button" id="scheduleSearchClear" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y text-muted p-0 pe-3 border-0 bg-transparent {{ request('search') ? '' : 'd-none' }}" title="Clear search" aria-label="Clear search">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            <select name="type" class="form-select" style="width: auto;" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()" aria-label="Machine type">
                <option value="">All Machine Types</option>
                @foreach($machineTypes as $type)
                <option value="{{ $type }}" @selected(strcasecmp((string) request('type'), $type) === 0)>{{ $type }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select" style="width: auto;" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="denied" @selected(request('status') === 'denied')>Denied</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            </select>
            @if(request()->anyFilled(['search', 'type', 'status']))
            <a href="{{ route('admin.schedule') }}" class="btn btn-link btn-sm">Clear filters</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="d-none px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Schedule ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                <tr>
                    <td class="d-none px-4 px-md-6 py-4 fw-medium text-dark">SCH-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $schedule->display_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->location }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($schedule->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#viewScheduleModal{{ $schedule->id }}">View</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">{{ request()->anyFilled(['search', 'type', 'status']) ? 'No schedules match your search or filters.' : 'No schedules recorded yet.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination-footer :paginator="$schedules" />
</div>

{{-- Modals live outside the table: a <div> can't be a direct child of <tbody>. --}}
@foreach($schedules as $schedule)
<x-modal id="viewScheduleModal{{ $schedule->id }}" title="Schedule Details">
    <div class="row g-3">
        <div class="d-none col-6"><label class="text-muted small d-block">Schedule ID</label><p class="fw-medium mb-0">SCH-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Status</label><p class="fw-medium mb-0"><x-status-badge :status="ucfirst($schedule->status)" /></p></div>
        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $schedule->display_name }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Machinery</label><p class="fw-medium mb-0">{{ $schedule->machinery }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Land Size</label><p class="fw-medium mb-0">{{ $schedule->land_size }} ha</p></div>
        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $schedule->scheduled_date->format('M d, Y') }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">{{ $schedule->location }}</p></div>
        @if($schedule->remarks)
        <div class="col-12"><label class="text-muted small d-block">Remarks</label><p class="fw-medium mb-0">{{ $schedule->remarks }}</p></div>
        @endif
    </div>
</x-modal>
@endforeach

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This view is for oversight of machinery usage across the cooperative. Schedule requests are created and processed by the Manager to avoid conflicts and support efficient planning.
</x-info-banner>

<script>
    // Live search — same behaviour as the other list searches: results update
    // shortly after you stop typing, Enter searches immediately, and the
    // clear (x) button resets it. The dropdowns submit on change.
    (function () {
        var form = document.getElementById('scheduleFilterForm');
        var input = document.getElementById('scheduleSearchInput');
        var icon = document.getElementById('scheduleSearchIcon');
        var clearBtn = document.getElementById('scheduleSearchClear');
        if (!form || !input) return;
        var timer;

        form.addEventListener('submit', function () {
            clearTimeout(timer);
            icon.className = 'fas fa-spinner fa-spin position-absolute start-3 top-50 translate-middle-y text-muted';
            input.readOnly = true;
        });

        input.addEventListener('input', function () {
            clearBtn.classList.toggle('d-none', !input.value);
            clearTimeout(timer);
            timer = setTimeout(function () {
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }, 500);
        });

        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.classList.add('d-none');
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });

        // After a search reloads the page, keep typing where you left off.
        if (input.value) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    })();
</script>
@endsection
