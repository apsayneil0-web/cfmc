{{-- "Needs a crop" switch + crop checkboxes for the Add/Edit Machinery forms.
     $machine is null on the Add form. --}}
@php
    $requiresCrop = $machine ? $machine->requires_crop : (bool) old('requires_crop', true);
    $selectedCropIds = $machine ? $machine->crops->pluck('id')->all() : array_map('intval', (array) old('crop_ids', []));
@endphp
<div class="col-12 machine-crop-settings">
    <div class="border rounded-3 p-3">
        <input type="hidden" name="requires_crop" value="0">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input machine-requires-crop" type="checkbox" role="switch" name="requires_crop" value="1" id="{{ $prefix }}RequiresCrop" {{ $requiresCrop ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="{{ $prefix }}RequiresCrop">Needs a crop</label>
        </div>
        <small class="text-muted d-block">On for field machines: farmers pick a crop to request it. Off for general-use machines (truck, hauling) that are borrowed without a crop or land size.</small>
        <div class="machine-crop-list mt-3 {{ $requiresCrop ? '' : 'd-none' }}">
            <label class="form-label fw-semibold mb-1">Works with these crops</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($crops as $crop)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="crop_ids[]" value="{{ $crop->id }}" id="{{ $prefix }}Crop{{ $crop->id }}" {{ in_array($crop->id, $selectedCropIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $prefix }}Crop{{ $crop->id }}">{{ $crop->name }}</label>
                </div>
                @endforeach
            </div>
            <small class="text-muted d-block mt-1">Leave all unchecked if it works with every crop.</small>
        </div>
    </div>
</div>
