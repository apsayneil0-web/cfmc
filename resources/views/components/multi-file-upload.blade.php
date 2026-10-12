{{--
    A document upload field that accepts one or more files: starts with one
    dropzone, and a "+ Add Another File" link appends more. Existing files
    (edit mode) list above it, each with its own "Remove" checkbox that
    reports back the exact path to delete.

    <x-multi-file-upload field="certificate_of_title" label="Upload Certificate of Title"
        base-id="editCertificate{{ $farmer->id }}" :existing-paths="$farmer->documentPaths('certificate_of_title_path')" required />
--}}
@props(['field', 'label', 'baseId', 'existingPaths' => [], 'required' => false])

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-multi-upload]').forEach(initMultiFileUpload);
    });

    function initMultiFileUpload(container) {
        var slotsContainer = container.querySelector('[data-multi-upload-slots]');
        var template = container.querySelector('template');
        var addBtn = container.querySelector('[data-multi-upload-add]');

        function addSlot(removable) {
            var fragment = template.content.cloneNode(true);
            var slot = fragment.querySelector('[data-multi-upload-slot]');
            var input = fragment.querySelector('[data-multi-upload-input]');
            var filenameEl = fragment.querySelector('[data-multi-upload-filename]');
            var removeBtn = fragment.querySelector('[data-multi-upload-remove]');

            if (removable) {
                removeBtn.classList.remove('d-none');
                removeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    slot.remove();
                });
            }

            slot.addEventListener('click', function (e) {
                if (e.target === removeBtn || e.target.closest('[data-multi-upload-remove]')) return;
                input.click();
            });
            input.addEventListener('change', function () {
                var file = input.files[0];
                filenameEl.innerHTML = file ? '<i class="fas fa-file"></i> ' + file.name : '';
            });
            slot.addEventListener('dragover', function (e) { e.preventDefault(); slot.classList.add('bg-white', 'border-success'); });
            slot.addEventListener('dragleave', function (e) { e.preventDefault(); slot.classList.remove('bg-white', 'border-success'); });
            slot.addEventListener('drop', function (e) {
                e.preventDefault();
                slot.classList.remove('bg-white', 'border-success');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    filenameEl.innerHTML = '<i class="fas fa-file"></i> ' + e.dataTransfer.files[0].name;
                }
            });

            slotsContainer.appendChild(fragment);
        }

        addSlot(false);
        addBtn.addEventListener('click', function () { addSlot(true); });
    }
</script>
@endonce

<div class="mb-3" data-multi-upload>
    <div class="d-flex align-items-center justify-content-between mb-1">
        <label class="form-label fw-semibold mb-0">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-multi-upload-add>
            <i class="fas fa-plus-circle me-1"></i>Add Another File
        </button>
    </div>

    @foreach($existingPaths as $path)
    <div class="border rounded-3 p-2 mb-2 d-flex align-items-center justify-content-between bg-light">
        <span class="small text-success"><i class="fas fa-check-circle me-1"></i>Current: {{ basename($path) }}</span>
        <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input" name="remove_{{ $field }}[]" value="{{ $path }}" id="{{ $baseId }}_remove_{{ $loop->index }}">
            <label class="form-check-label text-danger small" for="{{ $baseId }}_remove_{{ $loop->index }}">Remove</label>
        </div>
    </div>
    @endforeach

    <div data-multi-upload-slots></div>

    <template>
        <div class="border border-2 borderdashed rounded-3 p-3 text-center bg-light position-relative mb-2" data-multi-upload-slot>
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 d-none" data-multi-upload-remove title="Remove this file" style="font-size: 0.65rem;"></button>
            <i class="fas fa-cloud-upload-alt text-muted mb-2 d-block"></i>
            <p class="fw-medium text-dark mb-1 small">Click to upload or drag and drop</p>
            <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
            <input type="file" name="{{ $field }}[]" class="d-none" accept=".pdf,.jpg,.jpeg,.png" data-multi-upload-input>
            <div class="mt-2 text-success fw-medium small" data-multi-upload-filename></div>
        </div>
    </template>
</div>
