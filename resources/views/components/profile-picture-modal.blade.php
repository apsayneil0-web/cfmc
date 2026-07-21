@php
    $authUser = auth()->user();
@endphp

<!-- Profile Picture Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="profilePictureModalLabel">
                    <i class="fas fa-camera me-2 text-primary"></i>Update Profile Picture
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="profilePicturePreview" src="{{ $authUser->profile_picture_url ?? '' }}" alt="Preview" class="rounded-circle mb-3 {{ $authUser->profile_picture_url ? '' : 'd-none' }}" style="width: 8rem; height: 8rem; object-fit: cover;">
                <x-avatar-initials id="profilePictureFallback" :name="$authUser->name" color="primary" size="32" class="mx-auto mb-3 {{ $authUser->profile_picture_url ? 'd-none' : '' }}" />

                <input type="file" class="form-control" id="profilePictureInput" accept="image/*">
                <div class="form-text">JPG or PNG, up to 2MB.</div>
                <div class="small text-danger mt-2 d-none" id="profilePictureError"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="profilePictureSaveBtn" disabled>
                    <i class="fas fa-check me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('profilePictureInput');
        var preview = document.getElementById('profilePicturePreview');
        var fallback = document.getElementById('profilePictureFallback');
        var saveBtn = document.getElementById('profilePictureSaveBtn');
        var errorEl = document.getElementById('profilePictureError');
        var modalEl = document.getElementById('profilePictureModal');

        input.addEventListener('change', function () {
            errorEl.classList.add('d-none');
            var file = input.files[0];
            saveBtn.disabled = !file;

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    fallback.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        saveBtn.addEventListener('click', function () {
            var file = input.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('profile_picture', file);

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

            fetch('{{ route('profile.picture.update') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
                .then(function (response) { return response.json().catch(function () { return { success: false, message: 'Unknown error' }; }); })
                .then(function (data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        errorEl.textContent = data.message || 'Failed to update profile picture.';
                        errorEl.classList.remove('d-none');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-check me-2"></i>Save';
                    }
                })
                .catch(function () {
                    errorEl.textContent = 'Failed to update profile picture.';
                    errorEl.classList.remove('d-none');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-check me-2"></i>Save';
                });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            input.value = '';
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-check me-2"></i>Save';
            errorEl.classList.add('d-none');
        });
    })();
</script>
