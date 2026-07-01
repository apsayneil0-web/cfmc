@extends('layouts.manager')

@section('content')
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px;
    }

    .dashboard-row {
        display: flex;
        flex-wrap: wrap;
        margin: -10px;
    }

    .dashboard-col {
        padding: 10px;
    }

    .dashboard-col-md-3 { flex: 0 0 25%; max-width: 25%; }
    .dashboard-col-md-6 { flex: 0 0 50%; max-width: 50%; }
    .dashboard-col-12 { flex: 0 0 100%; max-width: 100%; }

    @media (max-width: 992px) {
        .dashboard-col-md-3 { flex: 0 0 50%; max-width: 50%; }
        .dashboard-col-md-6 { flex: 0 0 100%; max-width: 100%; }
    }

    @media (max-width: 576px) {
        .dashboard-col-md-3 { flex: 0 0 100%; max-width: 100%; }
    }

    .mb-4 { margin-bottom: 24px; }
    .mt-4 { margin-top: 24px; }
    .mt-3 { margin-top: 16px; }

    .text-center { text-align: center; }

    /* Card Stats - Green Theme */
    .card h5 {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .card h2 {
        font-size: 32px;
        font-weight: 700;
        color: #14b8a6;
    }

    .card p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* Calendar - Green Theme */
    .calendar-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .calendar-header h5 {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .calendar-nav {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .current-month {
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        min-width: 140px;
        text-align: center;
    }

    .btn-calendar {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .btn-calendar:hover {
        background: #dcfce7;
    }

    .btn-calendar svg {
        color: #14b8a6;
    }

    .calendar-grid {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%);
        border-bottom: 1px solid #bbf7d0;
    }

    .calendar-weekdays div {
        padding: 12px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: #166534;
        text-transform: uppercase;
    }

    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .calendar-day {
        min-height: 100px;
        padding: 8px;
        border-right: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        transition: background 0.2s;
    }

    .calendar-day:nth-child(7n) {
        border-right: none;
    }

    .calendar-day:hover {
        background: #f8fafc;
    }

    .calendar-day.other-month {
        background: #f8fafc;
        opacity: 0.5;
    }

    .calendar-day.today {
        background: #f0fdf4;
    }

    .day-number {
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .today .day-number {
        background: #14b8a6;
        color: #ffffff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-events {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .event-item {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        color: #ffffff;
        background: #14b8a6;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: opacity 0.2s;
    }

    .event-item:hover {
        opacity: 0.8;
    }

    .event-meeting { background: #14b8a6; }
    .event-harvest { background: #f59e0b; }
    .event-maintenance { background: #64748b; }
    .event-training { background: #8b5cf6; }

    .calendar-legend {
        display: flex;
        gap: 20px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }

    @media (max-width: 768px) {
        .calendar-day {
            min-height: 60px;
            padding: 4px;
        }

        .day-number {
            font-size: 12px;
        }

        .event-item {
            font-size: 10px;
            padding: 2px 4px;
        }

        .calendar-weekdays div {
            padding: 8px 4px;
            font-size: 10px;
        }
    }
</style>

<div class="dashboard-container">

    <div class="page-header">
        <div class="header-content">
            <h1>Manager Dashboard</h1>
            <p>Real-time cooperative operations overview</p>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="dashboard-row">

        <div class="dashboard-col dashboard-col-md-3">
            <div class="card text-center">
                <h5>Total Farmers</h5>
                <h2>{{ $totalFarmers ?? 0 }}</h2>
            </div>
        </div>

        <div class="dashboard-col dashboard-col-md-3">
            <div class="card text-center">
                <h5>Pending Membership</h5>
                <h2>{{ $pendingMembership ?? 0 }}</h2>
            </div>
        </div>

        <div class="dashboard-col dashboard-col-md-3">
            <div class="card text-center">
                <h5>Schedule Requests</h5>
                <p>Pending: {{ $pendingSchedules ?? 0 }}</p>
                <p>Approved: {{ $approvedSchedules ?? 0 }}</p>
            </div>
        </div>

        <div class="dashboard-col dashboard-col-md-3">
            <div class="card text-center">
                <h5>Active Loans</h5>
                <h2>{{ $activeLoans ?? 0 }}</h2>
            </div>
        </div>

    </div>

    <!-- SCHEDULE CALENDAR -->
    <div class="dashboard-row mt-4">
        <div class="dashboard-col dashboard-col-12">
            <div class="calendar-card">
                <div class="calendar-header">
                    <h5>Schedule Calendar</h5>
                    <div class="calendar-nav">
                        <button type="button" class="btn-calendar" id="prevMonth">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                            </svg>
                        </button>
                        <span class="current-month" id="currentMonth">June 2026</span>
                        <button type="button" class="btn-calendar" id="nextMonth">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-days" id="calendarDays">
                        <!-- Calendar days will be generated by JavaScript -->
                    </div>
                </div>
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-color event-meeting"></span>
                        <span>Meeting</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color event-harvest"></span>
                        <span>Harvest</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color event-maintenance"></span>
                        <span>Maintenance</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color event-training"></span>
                        <span>Training</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECOND ROW -->
    <div class="dashboard-row mt-4">

        <div class="dashboard-col dashboard-col-md-6">
            <div class="card">
                <h5>Machinery Status</h5>
                <p>Available: {{ $availableMachinery ?? 0 }}</p>
                <p>Maintenance: {{ $maintenanceMachinery ?? 0 }}</p>
            </div>
        </div>

        <div class="dashboard-col dashboard-col-md-6">
            <div class="card">
                <h5>System Overview</h5>
                <p>This dashboard shows real-time cooperative operations.</p>
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];

    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    // Sample events data - will be replaced with dynamic data
    const events = [
        { date: '2026-06-05', title: 'Monthly Meeting', type: 'event-meeting' },
        { date: '2026-06-10', title: 'Harvest Day', type: 'event-harvest' },
        { date: '2026-06-15', title: 'Equipment Maintenance', type: 'event-maintenance' },
        { date: '2026-06-20', title: 'Farmer Training', type: 'event-training' },
        { date: '2026-06-25', title: 'Board Meeting', type: 'event-meeting' },
        { date: '2026-07-01', title: 'New Season Planning', type: 'event-meeting' },
        { date: '2026-07-12', title: 'Mid-Year Harvest', type: 'event-harvest' },
    ];

    function renderCalendar(month, year) {
        const calendarDays = document.getElementById('calendarDays');
        const currentMonthEl = document.getElementById('currentMonth');

        calendarDays.innerHTML = '';
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startingDay = firstDay.getDay();
        const totalDays = lastDay.getDate();

        const prevMonthLastDay = new Date(year, month, 0).getDate();

        // Previous month days
        for (let i = startingDay - 1; i >= 0; i--) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day other-month';
            dayDiv.innerHTML = `<span class="day-number">${prevMonthLastDay - i}</span>`;
            calendarDays.appendChild(dayDiv);
        }

        // Current month days
        for (let day = 1; day <= totalDays; day++) {
            const dayDiv = document.createElement('div');
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = day === currentDate.getDate() && month === currentDate.getMonth() && year === currentDate.getFullYear();

            dayDiv.className = 'calendar-day' + (isToday ? ' today' : '');

            let dayEventsHtml = '<div class="day-events">';
            const dayEvents = events.filter(e => e.date === dateStr);
            dayEvents.forEach(event => {
                dayEventsHtml += `<div class="event-item ${event.type}" title="${event.title}">${event.title}</div>`;
            });
            dayEventsHtml += '</div>';

            dayDiv.innerHTML = `
                <span class="day-number">${day}</span>
                ${dayEventsHtml}
            `;
            calendarDays.appendChild(dayDiv);
        }

        // Next month days
        const remainingDays = 42 - (startingDay + totalDays);
        for (let i = 1; i <= remainingDays; i++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day other-month';
            dayDiv.innerHTML = `<span class="day-number">${i}</span>`;
            calendarDays.appendChild(dayDiv);
        }
    }

    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar(currentMonth, currentYear);
    });

    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar(currentMonth, currentYear);
    });

    // Initial render
    renderCalendar(currentMonth, currentYear);
});
</script>
@endsection