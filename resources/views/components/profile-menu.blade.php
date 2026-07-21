@php
    $authUser = auth()->user();
@endphp

<div class="d-flex align-items-center gap-3">
    <button type="button" class="btn p-0 border-0 bg-transparent" id="profileAvatarBtn" data-bs-toggle="modal" data-bs-target="#profilePictureModal" title="Change profile picture" style="cursor: pointer;">
        <x-avatar-initials :name="$authUser->name" :src="$authUser->profile_picture_url" color="primary" size="10" />
    </button>
    <div class="d-none d-sm-block">
        <p class="text-sm font-medium text-gray-900 mb-0">{{ $authUser->name }}</p>
        <p class="text-xs text-gray-500 mb-0">{{ $authUser->role_name }}</p>
    </div>
</div>
