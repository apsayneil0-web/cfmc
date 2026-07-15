@php
    $isMember = old('member_type', $schedule->member_type ?? 'member') === 'member';
@endphp
<div class="schedule-form">
    <div class="mb-3">
        <label class="form-label fw-semibold d-block">Farmer Type <span class="text-danger">*</span></label>
        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="member_type" id="{{ $prefix }}_member" value="member" {{ $isMember ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $prefix }}_member">Member</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="member_type" id="{{ $prefix }}_nonmember" value="non-member" {{ !$isMember ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $prefix }}_nonmember">Non-member</label>
            </div>
        </div>
    </div>

    <div class="mb-3 member-only">
        <label class="form-label fw-semibold">Farmer (Member) <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select">
            <option value="">Select farmer...</option>
            @foreach($members as $member)
            <option value="{{ $member->account_user_id }}" {{ old('user_id', $schedule->user_id ?? '') == $member->account_user_id ? 'selected' : '' }}>{{ $member->full_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3 nonmember-only">
        <label class="form-label fw-semibold">Farmer Name <span class="text-danger">*</span></label>
        <input type="text" name="farmer_name" class="form-control" value="{{ old('farmer_name', !$isMember ? ($schedule->farmer_name ?? '') : '') }}" placeholder="Full name">
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Machinery <span class="text-danger">*</span></label>
            <select name="machinery" class="form-select" required>
                @foreach($machineryList as $machine)
                <option value="{{ $machine }}" {{ old('machinery', $schedule->machinery ?? '') == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Land Size (hectares) <span class="text-danger">*</span></label>
            <input type="number" step="0.1" min="0.1" name="land_size" class="form-control" value="{{ old('land_size', $schedule->land_size ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
            <input type="date" name="scheduled_date" class="form-control" min="{{ \App\Models\ScheduleRequest::earliestAllowedDate()->format('Y-m-d') }}" value="{{ old('scheduled_date', $schedule?->scheduled_date?->format('Y-m-d')) }}" required>
            <small class="text-muted">Must be at least {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days from today.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $schedule?->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $schedule?->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '') }}" required>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
            <input type="text" name="location" class="form-control" value="{{ old('location', $schedule->location ?? '') }}" placeholder="e.g. Brgy. San Jose" required>
        </div>
    </div>
</div>
