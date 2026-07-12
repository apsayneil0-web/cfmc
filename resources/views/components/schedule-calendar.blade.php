@props([
    'calendarDays',
    'firstWeekday',
    'daysInMonth',
    'showNames' => false,
    'showOpenBadge' => false,
    'minHeight' => '110px',
    'compact' => false,
])

@php
    $dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $statusClass = $showNames ? 'calendar-booking-approved' : 'calendar-booking-booked';
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
            <div class="calendar-cell {{ $calendarDays[$day]->isNotEmpty() ? 'has-bookings' : '' }}" style="min-height: {{ $minHeight }};">
                <p class="calendar-cell-date">{{ $day }}</p>
                @forelse($calendarDays[$day] as $booking)
                @php
                    $label = $showNames ? $booking->display_name.' - '.$booking->machinery : $booking->machinery;
                    $timeRange = \Carbon\Carbon::parse($booking->start_time)->format('g:iA').'-'.\Carbon\Carbon::parse($booking->end_time)->format('g:iA');
                @endphp
                <div class="calendar-booking {{ $statusClass }}" title="{{ $label }} ({{ $timeRange }})">
                    <span class="calendar-booking-dot"></span>
                    <span class="calendar-booking-text">
                        <p class="calendar-booking-title">{{ $label }}</p>
                        <p class="calendar-booking-time">{{ $timeRange }}</p>
                    </span>
                </div>
                @empty
                    @if($showOpenBadge)
                    <span class="badge bg-success-subtle text-success border border-success-subtle calendar-open-badge">Open</span>
                    @endif
                @endforelse
            </div>
            @endfor
        </div>
    </div>
</div>
