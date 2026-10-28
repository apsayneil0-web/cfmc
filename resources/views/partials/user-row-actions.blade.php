{{--
    User Management row actions: one clear button (View), everything else in
    "More", grouped Change / Status / Remove. An action shown as a button never
    repeats inside More. Flags say what this role may do to this user:
    $canManage (view/edit), $canChangePassword, $canToggleStatus, $canUnlock.
--}}
@php
    $isArchived = $user->status === 'archived';
    $showChange = ! $isArchived && ($canManage || $canChangePassword);
    $showStatus = ! $isArchived && (($canUnlock && $user->status === 'locked') || ($canToggleStatus && in_array($user->status, ['active', 'inactive'], true)));
@endphp
<div class="d-inline-flex align-items-center gap-2">
    @if($canManage)
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewUser({{ $user->id }})">View</button>
    @endif
    <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
        <ul class="dropdown-menu dropdown-menu-end grouped-menu">
            @if($showChange)
            <li><h6 class="dropdown-header">Change</h6></li>
            @if($canManage)
            <li><button type="button" class="dropdown-item" onclick="editUser({{ $user->id }})"><i class="fas fa-pen fa-fw me-2 text-gray-500"></i>Edit details</button></li>
            @endif
            @if($canChangePassword)
            <li><button type="button" class="dropdown-item" onclick="openChangePassword({{ $user->id }}, {{ Js::from($user->name) }})"><i class="fas fa-lock fa-fw me-2 text-gray-500"></i>Change password</button></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            @endif

            @if($showStatus)
            <li><h6 class="dropdown-header">Status</h6></li>
            @if($canUnlock && $user->status === 'locked')
            <li><button type="button" class="dropdown-item" onclick="unlockUser({{ $user->id }})"><i class="fas fa-unlock fa-fw me-2 text-gray-500"></i>Unlock account</button></li>
            @endif
            @if($canToggleStatus && $user->status === 'active')
            <li><button type="button" class="dropdown-item" onclick="toggleUserStatus({{ $user->id }}, 'active')"><i class="fas fa-user-slash fa-fw me-2 text-gray-500"></i>Deactivate account</button></li>
            @elseif($canToggleStatus && $user->status === 'inactive')
            <li><button type="button" class="dropdown-item" onclick="toggleUserStatus({{ $user->id }}, 'inactive')"><i class="fas fa-check fa-fw me-2 text-gray-500"></i>Activate account</button></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            @endif

            <li><h6 class="dropdown-header">{{ $isArchived ? 'Restore' : 'Remove' }}</h6></li>
            @if($isArchived)
            <li><button type="button" class="dropdown-item" onclick="unarchiveUser({{ $user->id }})"><i class="fas fa-box-open fa-fw me-2 text-success"></i>Unarchive account</button></li>
            @else
            <li><button type="button" class="dropdown-item text-danger" onclick="archiveUser({{ $user->id }}, {{ Js::from($user->name) }})"><i class="fas fa-trash-can fa-fw me-2"></i>Archive account</button></li>
            @endif
        </ul>
    </div>
</div>
