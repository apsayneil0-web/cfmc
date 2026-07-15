@props([
    'notifications' => collect(),
    'unreadCount' => 0,
])

<div class="dropdown">
    <button class="icon-btn text-secondary position-relative" type="button" id="notificationBellToggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
        <span class="notification-badge" id="notificationBadge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" aria-labelledby="notificationBellToggle">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <h6 class="mb-0 fw-semibold">🔔 Notifications</h6>
            @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
            </form>
            @endif
        </div>
        <div class="notification-list">
            @forelse($notifications as $notification)
            @php $display = \App\Models\Notification::typeDisplay($notification->type); @endphp
            <button type="button"
                class="notification-item w-100 text-start border-0 border-bottom px-3 py-2 {{ $notification->is_read ? '' : 'is-unread' }}"
                data-bs-toggle="modal" data-bs-target="#notifDetail{{ $notification->id }}"
                onclick="markNotificationRead({{ $notification->id }}, this)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <p class="mb-1 fw-semibold text-dark small">{{ $display['icon'] }} {{ $notification->title }}</p>
                    @if(!$notification->is_read)
                    <span class="notification-dot flex-shrink-0"></span>
                    @endif
                </div>
                <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                <p class="mb-0 text-muted notification-time">{{ $notification->created_at->diffForHumans() }}</p>
            </button>
            @empty
            <div class="px-3 py-4 text-center text-muted small">No announcements yet.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function markNotificationRead(id, el) {
        el.classList.remove('is-unread');
        var dot = el.querySelector('.notification-dot');
        if (dot) dot.remove();

        var url = @json(route('notifications.mark-read', ['notification' => '__ID__'])).replace('__ID__', id);

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        }).then(function () {
            var remaining = document.querySelectorAll('.notification-item.is-unread').length;
            var badge = document.getElementById('notificationBadge');
            if (badge) {
                if (remaining > 0) {
                    badge.textContent = remaining > 9 ? '9+' : remaining;
                } else {
                    badge.remove();
                }
            }
        }).catch(function () {});
    }
</script>
