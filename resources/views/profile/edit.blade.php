@extends($user->layoutView())

@section('title', 'My Profile')
@section('header', 'My Profile')

@section('content')
@if(session('status'))
<x-info-banner variant="success" title="Done" class="mb-4">
    {{ session('status') }}
</x-info-banner>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 p-md-6 text-center">
            <x-avatar-initials :name="$user->name" :src="$user->profile_picture_url" color="primary" size="24" class="mx-auto mb-3" />
            <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
            <p class="text-muted small mb-3">{{ $user->role_name }}</p>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                <i class="fas fa-camera me-1"></i> Change Photo
            </button>

            <hr class="my-4">

            <div class="text-start small">
                <p class="text-muted mb-1">Username</p>
                <p class="fw-medium mb-3">@ {{ $user->username }}</p>
                <p class="text-muted mb-1">Email</p>
                <p class="fw-medium mb-0">{{ $user->email ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 p-md-6 mb-4">
            <h6 class="fw-bold mb-3">Contact Information</h6>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="Phonenumber" class="form-control @error('Phonenumber') is-invalid @enderror" value="{{ old('Phonenumber', $user->Phonenumber) }}" placeholder="09123456789">
                    @error('Phonenumber')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save Phone Number</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 p-md-6">
            <h6 class="fw-bold mb-3">Change Password</h6>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
