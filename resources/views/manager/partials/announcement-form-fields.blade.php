@php
    $isSelected = old('audience', $announcement->audience ?? 'all_members') === 'selected';
    $isMeeting = old('purpose', $announcement->purpose ?? 'Information') === 'Meeting';
    $selectedFarmerIds = old('farmer_ids', $announcement?->recipients->pluck('id')->all() ?? []);
@endphp
<div class="announcement-form">
    <div class="mb-3">
        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title ?? '') }}" placeholder="Enter title" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" rows="3" class="form-control" placeholder="Enter description">{{ old('description', $announcement->description ?? '') }}</textarea>
    </div>
    <div class="row mb-3">
        <div class="col-md-6 mb-3 mb-md-0">
            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
            <select name="purpose" class="form-select purpose-select" required>
                @foreach($purposes as $purpose)
                <option value="{{ $purpose }}" {{ old('purpose', $announcement->purpose ?? 'Information') == $purpose ? 'selected' : '' }}>{{ $purpose }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date</label>
            <input type="date" name="announcement_date" class="form-control" value="{{ old('announcement_date', $announcement?->announcement_date?->format('Y-m-d')) }}">
        </div>
    </div>
    <div class="row mb-3 meeting-fields" style="{{ $isMeeting ? '' : 'display:none;' }}">
        <div class="col-md-6 mb-3 mb-md-0">
            <label class="form-label fw-semibold">Time</label>
            <input type="time" name="time" class="form-control" value="{{ old('time', $announcement?->time ? \Carbon\Carbon::parse($announcement->time)->format('H:i') : '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Location</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. CFMC Office" value="{{ old('location', $announcement->location ?? '') }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Resolution (Optional)</label>
        <textarea name="resolution" rows="2" class="form-control" placeholder="Enter resolution details">{{ old('resolution', $announcement->resolution ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Select Farmers</label>
        <select name="audience" class="form-select audience-select">
            <option value="all_members" {{ !$isSelected ? 'selected' : '' }}>All Members</option>
            <option value="selected" {{ $isSelected ? 'selected' : '' }}>Selected Farmers</option>
        </select>
    </div>
    <div class="mb-3 farmer-picker" style="{{ $isSelected ? '' : 'display:none;' }}">
        <label class="form-label fw-semibold">Farmers <span class="text-danger">*</span></label>
        <div class="border rounded-lg p-2" style="max-height: 180px; overflow-y: auto;">
            @forelse($farmers as $farmer)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="farmer_ids[]" value="{{ $farmer->id }}"
                    id="{{ $prefix }}_farmer{{ $farmer->id }}" {{ in_array($farmer->id, $selectedFarmerIds) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $prefix }}_farmer{{ $farmer->id }}">{{ $farmer->full_name }}</label>
            </div>
            @empty
            <p class="text-muted small mb-0">No approved members available.</p>
            @endforelse
        </div>
    </div>
    <div>
        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            <option value="draft" {{ old('status', $announcement->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $announcement->status ?? 'draft') == 'published' ? 'selected' : '' }}>Published</option>
        </select>
    </div>
</div>
