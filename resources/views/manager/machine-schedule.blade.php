@extends('manager.layout')

@section('title', 'Machine Scheduling')
@section('header', 'Machine Scheduling')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    {{ session('error') }}
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

@php
    // Links keep the current month/machine/status/view, minus pagination.
    $scheduleUrl = fn (array $changes = []) => route('manager.machine-schedule', array_filter(
        array_merge(request()->except('page'), $changes),
        fn ($value) => $value !== null && $value !== ''
    ));
    $prevMonth = $selectedMonth->copy()->subMonthNoOverflow()->format('Y-m');
    $nextMonth = $selectedMonth->copy()->addMonthNoOverflow()->format('Y-m');
    $statusFilters = ['' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'completed' => 'Completed', 'denied' => 'Denied'];
    $statusDots = ['approved' => 'bg-success', 'pending' => 'bg-warning', 'completed' => 'bg-info', 'denied' => 'bg-danger'];
    $machineStatusLabels = ['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance'];
    $today = now()->startOfDay();
@endphp

<!-- Page actions -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <p class="text-sm text-gray-500 mb-0">See who is using which machine and when. Click a machine or a day to focus.</p>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if($viewMode === 'month')
        <button type="button" id="moveOneDayBtn" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2 text-nowrap" title="Pick specific date(s) to move">
            <i class="far fa-calendar"></i><span>Move One Day</span>
        </button>
        @endif
        <div class="btn-group btn-group-sm" role="group" aria-label="Move every schedule">
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#shiftDayBackwardModal" title="Move every schedule back 1 day">
                <i class="fas fa-backward"></i><span>&minus;1 Day</span>
            </button>
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#shiftDayModal" title="Move every schedule forward 1 day">
                <span>+1 Day</span><i class="fas fa-forward"></i>
            </button>
        </div>
        <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus"></i><span>Add Schedule</span>
        </button>
    </div>
</div>

<!-- Machine strip -->
<div class="machine-strip mb-5">
    <a href="{{ $scheduleUrl(['machinery' => null, 'day' => null]) }}" class="machine-strip-card {{ $machineryFilter ? '' : 'active' }}">
        <span class="machine-strip-icon"><i class="fas fa-table-cells-large"></i></span>
        <span style="min-width: 0;">
            <span class="machine-strip-name">All machines</span>
            <span class="machine-strip-meta"><span class="dot bg-secondary"></span>{{ $machineCards->count() }} {{ Str::plural('machine', $machineCards->count()) }}</span>
            <span class="machine-strip-meta">{{ $machineCards->sum('bookings') }} {{ Str::plural('booking', $machineCards->sum('bookings')) }} in {{ $selectedMonth->format('M') }}</span>
        </span>
    </a>
    @foreach($machineCards as $card)
    @php $cardType = strtolower($card->type ?? ''); @endphp
    <a href="{{ $scheduleUrl(['machinery' => $card->name, 'day' => null]) }}" class="machine-strip-card {{ $machineryFilter === $card->name ? 'active' : '' }}">
        <span class="machine-strip-icon {{ str_contains($cardType, 'seed') || str_contains($cardType, 'plant') ? 'is-wheat' : '' }}">
            <x-machine-illustration :type="$card->type" />
        </span>
        <span style="min-width: 0;">
            <span class="machine-strip-name">{{ $card->name }}</span>
            <span class="machine-strip-meta">
                <span class="dot {{ $card->status === 'maintenance' ? 'bg-danger' : ($card->status === 'in_use' ? 'bg-info' : 'bg-success') }}"></span>{{ $machineStatusLabels[$card->status] ?? ucfirst($card->status) }}
            </span>
            <span class="machine-strip-meta">{{ $card->bookings }} {{ Str::plural('booking', $card->bookings) }} in {{ $selectedMonth->format('M') }}</span>
        </span>
    </a>
    @endforeach
</div>

<div class="grid grid-cols-1 {{ $viewMode === 'month' ? 'xl:grid-cols-[minmax(0,1fr)_22rem]' : '' }} gap-5">
    <div class="section-card mb-0" style="min-width: 0;">
        <!-- Calendar header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 px-md-5 pt-4 pb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ $scheduleUrl(['month' => $prevMonth, 'day' => null]) }}" class="btn btn-outline-secondary btn-sm px-2" aria-label="Previous month"><i class="fas fa-chevron-left"></i></a>
                <h3 class="text-xl font-semibold text-gray-900 mb-0 text-center" style="min-width: 10rem;">{{ $selectedMonth->format('F Y') }}</h3>
                <a href="{{ $scheduleUrl(['month' => $nextMonth, 'day' => null]) }}" class="btn btn-outline-secondary btn-sm px-2" aria-label="Next month"><i class="fas fa-chevron-right"></i></a>
                <a href="{{ $scheduleUrl(['month' => null, 'day' => null]) }}" class="btn btn-outline-secondary btn-sm ms-1">Today</a>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @foreach($statusFilters as $value => $label)
                <a href="{{ $scheduleUrl(['status' => $value ?: null]) }}" class="filter-chip filter-chip-sm {{ $statusFilter === $value ? 'active' : '' }}">
                    @if($value)<span class="dot {{ $statusDots[$value] }}"></span>@endif{{ $label }}
                </a>
                @endforeach
                <div class="segmented ms-1" role="group" aria-label="Layout">
                    <a href="{{ $scheduleUrl(['view' => null]) }}" class="{{ $viewMode === 'month' ? 'active' : '' }}">Month</a>
                    <a href="{{ $scheduleUrl(['view' => 'list']) }}" class="{{ $viewMode === 'list' ? 'active' : '' }}">List</a>
                </div>
            </div>
        </div>

        @if($viewMode === 'month')
        <!-- Move One Day: selection toolbar (shown while picking dates) -->
        <div id="moveOneDayToolbar" class="d-none align-items-center justify-content-between gap-3 px-4 px-md-5 py-3 border-top border-bottom flex-wrap" style="background-color: var(--brand-warning-light);">
            <p class="mb-0 small fw-medium"><i class="fas fa-info-circle me-1"></i>Check the days you want to move, then confirm. <span id="moveOneDayCount">0 selected</span></p>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 small fw-medium">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="radio" name="moveOneDayDirection" id="moveOneDayForward" value="forward" checked>
                        <label class="form-check-label" for="moveOneDayForward">Forward</label>
                    </div>
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="radio" name="moveOneDayDirection" id="moveOneDayBackward" value="backward">
                        <label class="form-check-label" for="moveOneDayBackward">Backward</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="moveOneDayCancel" class="btn btn-sm btn-outline-secondary">Cancel</button>
                    <button type="button" id="moveOneDayConfirm" class="btn btn-sm btn-warning" disabled>Move Selected Day(s)</button>
                </div>
            </div>
        </div>

        <!-- Month grid -->
        <div class="px-3 px-md-5 pb-5">
            <div class="calendar-scroll">
                <div class="calendar-grid schedule-month">
                    <div class="calendar-grid-head">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $label)
                        <div class="calendar-day-label">{{ $label }}</div>
                        @endforeach
                    </div>
                    <div class="calendar-grid-body">
                        @for($i = 0; $i < $firstWeekday; $i++)
                        <div class="calendar-cell calendar-cell-empty"></div>
                        @endfor
                        @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $cellDate = $selectedMonth->copy()->startOfMonth()->addDays($day - 1);
                            $cellBookings = $bookingsByDay->get($day, collect());
                            $movable = $movableByDay->get($day, 0);
                            $isSelectedDay = $cellDate->isSameDay($selectedDay);
                        @endphp
                        <div class="calendar-cell schedule-day {{ $cellBookings->isNotEmpty() ? 'has-bookings' : '' }} {{ $isSelectedDay ? 'is-focused' : '' }}"
                            data-day-url="{{ $scheduleUrl(['day' => $cellDate->format('Y-m-d')]) }}" role="link" tabindex="0"
                            aria-label="{{ $cellDate->format('F j') }}, {{ $cellBookings->count() }} {{ Str::plural('booking', $cellBookings->count()) }}">
                            @if($movable > 0 && $cellDate->gte($today))
                            <label class="calendar-cell-select" title="Select {{ $cellDate->format('M d, Y') }} to move">
                                <input type="checkbox" class="calendar-select-checkbox" value="{{ $cellDate->format('Y-m-d') }}" data-count="{{ $movable }}">
                            </label>
                            @endif
                            <p class="calendar-cell-date {{ $cellDate->isSameDay($today) ? 'is-today' : '' }}">{{ $day }}</p>
                            @foreach($cellBookings->take(2) as $booking)
                            <span class="schedule-chip is-{{ $booking->status }}" title="{{ $booking->display_name }} · {{ $booking->machinery }} ({{ ucfirst($booking->status) }})">
                                <strong>{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</strong> {{ $booking->machinery }}
                            </span>
                            @endforeach
                            @if($cellBookings->count() > 2)
                            <span class="schedule-chip-more">+{{ $cellBookings->count() - 2 }} more</span>
                            @endif
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Schedule list -->
        <div class="table-toolbar d-flex align-items-center justify-content-between gap-3 border-top">
            <h3 class="text-lg font-semibold text-gray-900 mb-0">{{ $showArchived ? 'Archived Schedules' : 'All Schedules' }}</h3>
            <x-archive-toggle route="manager.machine-schedule" :showing="$showArchived" :count="$archivedCount" :except="['page']" />
        </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="d-none px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Schedule ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date & Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="d-none px-4 px-md-6 py-4 fw-medium text-dark">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $req->display_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->units_requested > 1 ? $req->units_requested.'× ' : '' }}{{ $req->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }} - {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        @php $canComplete = ! $showArchived && $req->status === 'approved'; @endphp
                        <x-action-dropdown>
                            <x-slot:primary>
                                @if($canComplete)
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal{{ $req->id }}">Complete &amp; Record Yield</button>
                                @else
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}">View</button>
                                @endif
                            </x-slot:primary>
                            @if($canComplete)
                            <x-dropdown-item icon="fa-eye" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}">View</x-dropdown-item>
                            @endif
                            @if($showArchived)
                            <x-dropdown-item icon="fa-box-open" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $req->id }}">Restore</x-dropdown-item>
                            @else
                            <x-dropdown-item icon="fa-pen" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}">Edit</x-dropdown-item>
                            <li><hr class="dropdown-divider"></li>
                            <x-dropdown-item icon="fa-archive" :danger="true" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $req->id }}">Archive</x-dropdown-item>
                            @endif
                        </x-action-dropdown>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">{{ $showArchived ? 'No archived schedules.' : 'No schedules found for this month.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
        <x-pagination-footer :paginator="$requests" />
        @endif
    </div>

    @if($viewMode === 'month')
    <!-- Selected day -->
    <aside class="section-card mb-0 align-self-start">
        <div class="px-4 px-md-5 py-4 border-bottom">
            <p class="text-xs fw-semibold text-gray-500 text-uppercase mb-0" style="letter-spacing: 0.08em;">{{ $selectedDay->format('l') }}</p>
            <h3 class="text-2xl font-bold text-gray-900 mb-0">{{ $selectedDay->format('F j') }}</h3>
            <p class="text-sm text-gray-500 mb-0">{{ $dayBookings->count() }} {{ Str::plural('booking', $dayBookings->count()) }}</p>
        </div>
        <div class="p-3 p-md-4 d-flex flex-column gap-3">
            @forelse($dayBookings as $booking)
            <div class="day-booking">
                <div class="d-flex gap-3">
                    <span class="machine-strip-icon flex-shrink-0"><x-machine-illustration :type="optional($booking->machine)->type ?? $booking->machinery" /></span>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <p class="fw-semibold text-gray-900 mb-0 text-truncate">{{ $booking->units_requested > 1 ? $booking->units_requested.'× ' : '' }}{{ $booking->machinery }}</p>
                            <x-status-badge :status="ucfirst($booking->status)" class="flex-shrink-0" />
                        </div>
                        <p class="text-sm text-gray-700 mb-0">{{ $booking->display_name }} <span class="text-gray-500">&middot; {{ $booking->member_type === 'member' ? 'Member' : 'Non-member' }}</span></p>
                        <p class="text-sm text-gray-500 mb-0">
                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}&ndash;{{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    @if($booking->status === 'approved')
                    <button type="button" class="btn btn-primary btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#completeModal{{ $booking->id }}">Complete &amp; Record Yield</button>
                    @elseif($booking->status === 'pending')
                    <a href="{{ route('manager.schedule-approval') }}" class="btn btn-primary btn-sm flex-grow-1">Review request</a>
                    @else
                    <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $booking->id }}">View</button>
                    @endif
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(in_array($booking->status, ['approved', 'pending'], true))
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal{{ $booking->id }}"><i class="fas fa-eye me-2 text-gray-500"></i>View</button></li>
                            @endif
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal{{ $booking->id }}"><i class="fas fa-pen me-2 text-gray-500"></i>Edit</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $booking->id }}"><i class="fas fa-archive me-2"></i>Archive</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-muted text-center py-4 mb-0">No bookings on this day.</p>
            @endforelse
        </div>
    </aside>
    @endif
</div>

{{-- Modals live outside the table: a <div> directly inside <tbody> is invalid
     and browsers eject it, breaking the rows after it. --}}
@foreach($modalSchedules as $req)
@include('manager.partials.schedule-modals', ['req' => $req])
@endforeach

<!-- Move Schedule +1 Day Confirmation Modal -->
<div class="modal fade" id="shiftDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-forward me-2"></i>Move Schedule +1 Day</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-day') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>This will move <strong>every pending/approved schedule dated today or later</strong> forward by 1 day, and automatically notify each affected farmer of their new date. Schedules already in the past are left alone. This action cannot be undone. Continue?</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Fleet-wide rainout, machine breakdown, operator unavailable..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Yes, Move Schedules</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move Schedule -1 Day Confirmation Modal -->
<div class="modal fade" id="shiftDayBackwardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-backward me-2"></i>Move Schedule −1 Day</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-day-backward') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>This will move <strong>every pending/approved schedule dated today or later</strong> back by 1 day, and automatically notify each affected farmer of their new date. Schedules already in the past are left alone, and any that would land before the {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }}-day minimum lead time are skipped instead of being backdated. This action cannot be undone. Continue?</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Original date was scheduled in error, farmer requested earlier slot..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Yes, Move Schedules Back</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move One Day Confirmation Modal -->
<div class="modal fade" id="moveOneDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-day me-2"></i><span id="moveOneDayModalTitle">Move Selected Day(s)</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-specific-day') }}" method="POST">
                @csrf
                <input type="hidden" name="direction" id="moveOneDayDirectionInput" value="forward">
                <div id="moveOneDayHiddenInputs"></div>
                <div class="modal-body">
                    <p id="moveOneDaySummaryIntro">This will move every pending/approved schedule on the date(s) below forward by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking on the next day is skipped instead of overbooking. Continue?</p>
                    <ul id="moveOneDaySummary" class="mb-0 ps-3 small"></ul>
                    <div class="mb-0 mt-3">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Fleet-wide rainout, machine breakdown, operator unavailable..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Yes, Move</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('manager.partials.schedule-form-fields', ['prefix' => 'create', 'schedule' => null])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var SCHEDULE_HOURS_PER_HECTARE = {{ \App\Models\ScheduleRequest::HOURS_PER_HECTARE }};

    document.querySelectorAll('.schedule-form').forEach(function (form) {
        var radios = form.querySelectorAll('input[name="member_type"]');
        var memberBlocks = form.querySelectorAll('.member-only');
        var nonMemberBlocks = form.querySelectorAll('.nonmember-only');

        function toggle() {
            var isMember = form.querySelector('input[name="member_type"]:checked').value === 'member';
            memberBlocks.forEach(function (block) { block.classList.toggle('d-none', !isMember); });
            nonMemberBlocks.forEach(function (block) { block.classList.toggle('d-none', isMember); });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', toggle);
        });
        toggle();

        var landSizeInput = form.querySelector('input[name="land_size"]');
        var startTimeInput = form.querySelector('input[name="start_time"]');
        var endTimeInput = form.querySelector('input[name="end_time"]');
        var machinerySelect = form.querySelector('select[name="machinery"]');
        var unitsSelect = form.querySelector('select[name="units_requested"]');

        function estimateEndTime() {
            var landSize = parseFloat(landSizeInput.value);
            var startTime = startTimeInput.value;
            var units = parseInt(unitsSelect.value, 10) || 1;

            if (!landSize || landSize <= 0 || !startTime) {
                return;
            }

            var totalMinutes = Math.round((landSize / units) * SCHEDULE_HOURS_PER_HECTARE * 60 / 5) * 5;
            var parts = startTime.split(':');
            var end = new Date(0, 0, 0, parseInt(parts[0], 10), parseInt(parts[1], 10));
            end.setMinutes(end.getMinutes() + totalMinutes);

            endTimeInput.value = String(end.getHours()).padStart(2, '0') + ':' + String(end.getMinutes()).padStart(2, '0');
        }

        function refreshUnitsOptions() {
            var selectedOption = machinerySelect.options[machinerySelect.selectedIndex];
            var quantity = selectedOption ? (parseInt(selectedOption.dataset.quantity, 10) || 1) : 1;
            var desired = unitsSelect.dataset.initialized ? (parseInt(unitsSelect.value, 10) || 1) : (parseInt(unitsSelect.dataset.selected, 10) || 1);

            unitsSelect.innerHTML = '';
            for (var i = 1; i <= quantity; i++) {
                var option = document.createElement('option');
                option.value = i;
                option.textContent = i === 1 ? '1 unit' : (i + ' units (parallel)');
                unitsSelect.appendChild(option);
            }
            unitsSelect.value = Math.min(desired, quantity);
            unitsSelect.dataset.initialized = '1';

            estimateEndTime();
        }

        landSizeInput.addEventListener('input', estimateEndTime);
        startTimeInput.addEventListener('input', estimateEndTime);
        unitsSelect.addEventListener('change', estimateEndTime);
        machinerySelect.addEventListener('change', refreshUnitsOptions);
        refreshUnitsOptions();
    });

    // Clicking a day focuses it in the side panel; while picking days for
    // Move One Day the click toggles that day's checkbox instead.
    document.querySelectorAll('.schedule-day[data-day-url]').forEach(function (cell) {
        function activate(e) {
            if (e.target.closest('.calendar-cell-select')) {
                return;
            }
            if (cell.closest('.calendar-grid').classList.contains('is-selecting')) {
                var checkbox = cell.querySelector('.calendar-select-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                }
                return;
            }
            window.location.href = cell.dataset.dayUrl;
        }
        cell.addEventListener('click', activate);
        cell.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activate(e);
            }
        });
    });

    (function () {
        var moveBtn = document.getElementById('moveOneDayBtn');
        var toolbar = document.getElementById('moveOneDayToolbar');
        var cancelBtn = document.getElementById('moveOneDayCancel');
        var confirmBtn = document.getElementById('moveOneDayConfirm');
        var countLabel = document.getElementById('moveOneDayCount');
        var calendarGrid = document.querySelector('.calendar-grid');
        var hiddenInputs = document.getElementById('moveOneDayHiddenInputs');
        var summaryList = document.getElementById('moveOneDaySummary');
        var directionInput = document.getElementById('moveOneDayDirectionInput');
        var modalTitle = document.getElementById('moveOneDayModalTitle');
        var summaryIntro = document.getElementById('moveOneDaySummaryIntro');

        if (!moveBtn || !calendarGrid) {
            return;
        }

        function checkboxes() {
            return Array.prototype.slice.call(calendarGrid.querySelectorAll('.calendar-select-checkbox'));
        }

        function updateSelectionState() {
            var checked = checkboxes().filter(function (cb) { return cb.checked; });
            countLabel.textContent = checked.length + ' selected';
            confirmBtn.disabled = checked.length === 0;

            checkboxes().forEach(function (cb) {
                cb.closest('.calendar-cell').classList.toggle('is-day-selected', cb.checked);
            });
        }

        function exitSelectionMode() {
            calendarGrid.classList.remove('is-selecting');
            checkboxes().forEach(function (cb) {
                cb.checked = false;
                cb.closest('.calendar-cell').classList.remove('is-day-selected');
            });
            toolbar.classList.add('d-none');
            toolbar.classList.remove('d-flex');
            moveBtn.classList.remove('d-none');
            document.getElementById('moveOneDayForward').checked = true;
            updateSelectionState();
        }

        moveBtn.addEventListener('click', function () {
            calendarGrid.classList.add('is-selecting');
            toolbar.classList.remove('d-none');
            toolbar.classList.add('d-flex');
            moveBtn.classList.add('d-none');
            updateSelectionState();
        });

        cancelBtn.addEventListener('click', exitSelectionMode);

        calendarGrid.addEventListener('change', function (e) {
            if (e.target.classList.contains('calendar-select-checkbox')) {
                updateSelectionState();
            }
        });

        confirmBtn.addEventListener('click', function () {
            var checked = checkboxes().filter(function (cb) { return cb.checked; });

            if (checked.length === 0) {
                return;
            }

            var direction = document.querySelector('input[name="moveOneDayDirection"]:checked').value;
            var isBackward = direction === 'backward';

            directionInput.value = direction;
            modalTitle.textContent = isBackward ? 'Move Selected Day(s) Back' : 'Move Selected Day(s)';
            summaryIntro.textContent = isBackward
                ? 'This will move every pending/approved schedule on the date(s) below back by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking, or fall before the minimum lead time, is skipped instead.'
                : 'This will move every pending/approved schedule on the date(s) below forward by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking on the next day is skipped instead of overbooking.';

            hiddenInputs.innerHTML = '';
            summaryList.innerHTML = '';

            checked.forEach(function (cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'dates[]';
                input.value = cb.value;
                hiddenInputs.appendChild(input);

                var date = new Date(cb.value + 'T00:00:00');
                var formatted = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                var count = cb.dataset.count;

                var li = document.createElement('li');
                li.textContent = formatted + ' — ' + count + ' schedule(s)';
                summaryList.appendChild(li);
            });

            new bootstrap.Modal(document.getElementById('moveOneDayModal')).show();
        });
    })();
</script>
@endsection
