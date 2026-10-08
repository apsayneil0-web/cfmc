@php
    $authUser = auth()->user();
@endphp

<div class="dropdown">
    <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-3" type="button" id="profileMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
        <x-avatar-initials :name="$authUser->name" :src="$authUser->profile_picture_url" color="primary" size="10" />
        <div class="d-none d-sm-block text-start">
            <p class="text-sm font-medium text-gray-900 mb-0">{{ $authUser->name }}</p>
            <p class="text-xs text-gray-500 mb-0">{{ $authUser->role_name }}</p>
        </div>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileMenuToggle" style="min-width: 240px;">
        <div class="px-3 py-2">
            <p class="mb-0 fw-semibold text-dark">{{ $authUser->name }}</p>
            <p class="mb-0 text-muted small text-truncate">{{ $authUser->email ?? 'N/A' }}</p>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2 text-muted fa-fw"></i>My Profile</a>
        @if((int) $authUser->roleID === 1)
        <a class="dropdown-item" href="{{ route('admin.activity-logs') }}"><i class="fas fa-clock-rotate-left me-2 text-muted fa-fw"></i>Activity Logs</a>
        @endif
        <a class="dropdown-item" href="{{ route('settings.edit') }}"><i class="fas fa-gear me-2 text-muted fa-fw"></i>Settings</a>
        <div class="dropdown-divider"></div>
        <button type="button" class="dropdown-item text-danger" onclick="confirmLogout()"><i class="fas fa-arrow-right-from-bracket me-2 fa-fw"></i>Logout</button>
    </div>
</div>
