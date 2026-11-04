@props([
    'calendarDays',
    'firstWeekday',
    'daysInMonth',
    'showNames' => false,
    'showOpenBadge' => false,
    'minHeight' => '110px',
    'compact' => false,
    'month' => null,
    'minDate' => null,
    'clickable' => false,
    'bookedClass' => null,
    'selectionMode' => false,
    'highlightOwn' => false,
    'availableDays' => null,
])

@php
    $dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $statusClass = $bookedClass ?? ($showNames ? 'calendar-booking-approved' : 'calendar-booking-booked');
    $earliestDate = $minDate ? \Carbon\Carbon::parse($minDate)->startOfDay() : null;
@endphp

<div class="calendar-scroll">
    <div class="calendar-grid {{ $compact ? 'is-compact' : '' }}">
        <div class="calendar-grid-head">
            @foreach($dayLabels as $label)
            <div class="calendar-day-label">{{ $label }}</div>
            @endforeach
        </div>
        <div class="calendar-grid-body">
            @for($i = 0; $i < $firstWeekday; $i++)
            <div class="calendar-cell calendar-cell-empty" style="min-height: {{ $minHeight }};"></div>
            @endfor
            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $isOpen = $calendarDays[$day]->isEmpty();
                // $availableDays (farmer's schedule calendar only) says whether
                // some machine still has hectare/unit room left that day, even
                // with existing bookings — that's what decides clickability now.
                // Callers that don't pass it keep the old "zero bookings" rule.
                $hasCapacity = $availableDays[$day] ?? $isOpen;
                $cellDate = $month ? $month->copy()->startOfMonth()->addDays($day - 1) : null;
                $dateTooSoon = $earliestDate && $cellDate && $cellDate->lt($earliestDate);
                $isTooSoon = $isOpen && $dateTooSoon;
                $isSelectable = $clickable && $hasCapacity && !$dateTooSoon;
                $isPartial = $hasCapacity && $calendarDays[$day]->isNotEmpty();
            @endphp
            <div
                class="calendar-cell {{ $calendarDays[$day]->isNotEmpty() ? 'has-bookings' : '' }} {{ $isSelectable ? 'calendar-cell-clickable' : '' }} {{ $clickable && $dateTooSoon ? 'calendar-cell-disabled' : '' }}"
                style="min-height: {{ $minHeight }};"
                @if($isSelectable) data-date="{{ $cellDate->format('Y-m-d') }}" role="button" tabindex="0" @endif
            >
                @if($selectionMode && $calendarDays[$day]->isNotEmpty() && $cellDate && $cellDate->gte(now()->startOfDay()))
                <label class="calendar-cell-select" title="Select {{ $cellDate->format('M d, Y') }} to move">
                    <input type="checkbox" class="calendar-select-checkbox" value="{{ $cellDate->format('Y-m-d') }}" data-count="{{ $calendarDays[$day]->count() }}">
                </label>
                @endif
                <p class="calendar-cell-date">{{ $day }}</p>
                @forelse($calendarDays[$day] as $booking)
                @php
                    $machineryLabel = ($booking->units_requested > 1 ? $booking->units_requested.'× ' : '').$booking->machinery;
                    $label = $showNames ? $booking->display_name.' - '.$machineryLabel : $machineryLabel;
                    $timeRange = \Carbon\Carbon::parse($booking->start_time)->format('g:iA').'-'.\Carbon\Carbon::parse($booking->end_time)->format('g:iA');
                    $isOwnBooking = $booking->user_id === auth()->id();
                    $bookingClass = $highlightOwn
                        ? ($isOwnBooking ? 'calendar-booking-approved' : ($isPartial ? 'calendar-booking-partial' : 'calendar-booking-booked'))
                        : ($isPartial ? 'calendar-booking-partial' : $statusClass);
                @endphp
                <div class="calendar-booking {{ $bookingClass }}" title="{{ $label }} ({{ $timeRange }})">
                    <span class="calendar-booking-dot"></span>
                    <span class="calendar-booking-text">
                        <p class="calendar-booking-title">{{ $label }}</p>
                        <p class="calendar-booking-time">{{ $timeRange }}</p>
                    </span>
                </div>
                @empty
                    @if($showOpenBadge && $clickable && $isTooSoon)
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle calendar-open-badge">Too soon</span>
                    @elseif($showOpenBadge)
                    <span class="badge bg-success-subtle text-success border border-success-subtle calendar-open-badge">Open</span>
                    @endif
                @endforelse
                @if($showOpenBadge && $isPartial && !$dateTooSoon)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle calendar-open-badge">Still open</span>
                @endif
            </div>
            @endfor
        </div>
    </div>
</div>
