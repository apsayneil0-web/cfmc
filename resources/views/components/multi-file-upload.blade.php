{{--
    A document upload field that accepts one or more files: starts with one
    dropzone, and a "+ Add Another File" link appends more. Existing files
    (edit mode) list above it, each with its own "Remove" checkbox that
    reports back the exact path to delete.

    Every file picked or dropped is shown in an "Upload this file?" preview
    first (the photo itself, or the PDF), with its name and size; it is only
    kept once confirmed. Cancel puts back whatever was there before.

    <x-multi-file-upload field="certificate_of_title" label="Upload Certificate of Title"
        base-id="editCertificate{{ $farmer->id }}" :existing-paths="$farmer->documentPaths('certificate_of_title_path')" required />
--}}
@props(['field', 'label', 'baseId', 'existingPaths' => [], 'required' => false])

@once
<div class="ufc" id="uploadConfirm" hidden role="dialog" aria-modal="true" aria-labelledby="ufcTitle" aria-describedby="ufcFor">
    <div class="ufc-backdrop"></div>
    <div class="ufc-panel">
        <div class="ufc-head">
            <span class="ufc-icon"><i class="fas fa-cloud-upload-alt"></i></span>
            <div>
                <h2 class="ufc-title" id="ufcTitle">Upload this file?</h2>
                <p class="ufc-for" id="ufcFor"></p>
            </div>
        </div>
        <div class="ufc-preview" id="ufcPreview"></div>
        <div class="ufc-meta">
            <span class="ufc-name" id="ufcName"></span>
            <span class="ufc-size" id="ufcSize"></span>
        </div>
        <p class="ufc-error" id="ufcError" hidden></p>
        <div class="ufc-actions">
            <button type="button" class="btn btn-outline-secondary" id="ufcCancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="ufcConfirm"><i class="fas fa-check me-1"></i>Use this file</button>
        </div>
    </div>
</div>

<style>
    .ufc { position: fixed; inset: 0; z-index: 2100; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .ufc[hidden] { display: none; }
    .ufc-backdrop {
        position: absolute; inset: 0; background: var(--backdrop-tint, rgba(31, 42, 34, 0.55));
        -webkit-backdrop-filter: blur(var(--backdrop-blur, 6px)); backdrop-filter: blur(var(--backdrop-blur, 6px));
    }
    .ufc-panel {
        position: relative; width: min(30rem, 100%); max-height: calc(100vh - 2rem); overflow: auto;
        padding: 1.25rem 1.25rem 1.1rem; background: var(--brand-surface, #fff); color: var(--text-primary, #1f2a22);
        border: 1px solid var(--brand-border, #e4e2da); border-radius: var(--radius-lg, 1rem);
        box-shadow: var(--shadow-modal, 0 32px 64px -24px rgba(0, 0, 0, 0.45));
        animation: ufc-in 280ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ufc-head { display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; }
    .ufc-icon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border-radius: 999px; flex-shrink: 0; background: var(--brand-primary-light, #e6eee7); color: var(--brand-primary, #2f5d3a); }
    .ufc-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
    .ufc-for { margin: 0.1rem 0 0; font-size: 0.85rem; color: var(--text-muted, #6b7069); }
    .ufc-preview {
        display: grid; place-items: center; min-height: 12rem; max-height: 22rem; overflow: hidden;
        border: 1px solid var(--brand-border, #e4e2da); border-radius: var(--radius-md, 0.75rem); background: var(--brand-surface-muted, #f9f8f5);
    }
    .ufc-preview img { max-width: 100%; max-height: 22rem; object-fit: contain; display: block; }
    .ufc-preview object { width: 100%; height: 22rem; border: 0; }
    .ufc-file { text-align: center; padding: 2rem 1rem; color: var(--text-secondary, #4a5249); }
    .ufc-file i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; color: var(--brand-danger-text, #9c4532); }
    .ufc-meta { display: flex; justify-content: space-between; gap: 0.75rem; margin-top: 0.75rem; font-size: 0.875rem; }
    .ufc-name { font-weight: 600; overflow-wrap: anywhere; }
    .ufc-size { color: var(--text-muted, #6b7069); white-space: nowrap; font-variant-numeric: tabular-nums; }
    .ufc-error { margin: 0.75rem 0 0; padding: 0.6rem 0.8rem; border-radius: var(--radius-sm, 0.5rem); font-size: 0.85rem; background: var(--brand-danger-light, #f6e5e0); color: var(--brand-danger-text, #9c4532); }
    .ufc-actions { display: flex; gap: 0.6rem; margin-top: 1.1rem; }
    .ufc-actions .btn { flex: 1; }
    /* The chosen file, shown in place of the upload prompt */
    .mu-file { display: flex; align-items: center; gap: 0.75rem; text-align: left; padding: 0.15rem 0; }
    .mu-file[hidden] { display: none; }
    .mu-thumb {
        width: 3.25rem; height: 3.25rem; flex-shrink: 0; border-radius: 0.5rem; overflow: hidden;
        border: 1px solid var(--brand-border, #e4e2da); background: var(--brand-surface, #fff);
        display: grid; place-items: center; color: var(--brand-danger-text, #9c4532); font-size: 1.4rem;
    }
    .mu-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mu-info { min-width: 0; flex: 1; }
    .mu-name { display: block; font-weight: 600; font-size: 0.875rem; color: var(--text-primary, #1f2a22); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mu-sub { display: block; font-size: 0.78rem; color: var(--text-muted, #6b7069); }
    .mu-remove { flex-shrink: 0; }
    [data-multi-upload-slot].has-file { border-style: solid !important; background: var(--brand-surface, #fff) !important; }
    /* Extra boxes have a corner ✕ (removes the whole box); keep Remove clear of it. */
    [data-multi-upload-slot] .btn-close:not(.d-none) ~ .mu-file { margin-right: 1.5rem; }
    @keyframes ufc-in { from { opacity: 0; transform: translateY(12px) scale(0.97); } }
    @media (prefers-reduced-motion: reduce) { .ufc-panel { animation: none; } }
</style>

<script>
    // "Upload this file?" preview, shared by every upload box on the page.
    var uploadConfirm = (function () {
        var MAX_BYTES = 10 * 1024 * 1024;
        var ALLOWED = /\.(pdf|jpe?g|png)$/i;
        var box, home, resolver = null, objectUrl = null, lastFocus = null;

        function el(id) { return document.getElementById(id); }

        function humanSize(bytes) {
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function finish(result) {
            if (!box || box.hidden) return;
            box.hidden = true;
            el('ufcPreview').innerHTML = '';
            if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
            if (box.parentNode !== home) home.appendChild(box);
            if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
            var r = resolver; resolver = null;
            if (r) r(result);
        }

        function ask(file, forLabel) {
            box = box || el('uploadConfirm');
            home = home || box.parentNode;
            if (resolver) finish(false);

            el('ufcFor').textContent = forLabel ? 'For: ' + forLabel : '';
            el('ufcName').textContent = file.name;
            el('ufcSize').textContent = humanSize(file.size);

            var problem = !ALLOWED.test(file.name)
                ? 'This file type isn\'t accepted. Please choose a PDF, JPG or PNG file.'
                : (file.size > MAX_BYTES ? 'This file is ' + humanSize(file.size) + ', which is over the 10 MB limit. Please choose a smaller file.' : '');
            el('ufcError').textContent = problem;
            el('ufcError').hidden = !problem;
            el('ufcConfirm').hidden = !!problem;
            el('ufcCancel').textContent = problem ? 'Choose another file' : 'Cancel';
            el('ufcTitle').textContent = problem ? 'This file can\'t be uploaded' : 'Upload this file?';

            var preview = el('ufcPreview');
            preview.innerHTML = '';
            objectUrl = URL.createObjectURL(file);
            if (/^image\//.test(file.type)) {
                var img = new Image();
                img.alt = 'Preview of ' + file.name;
                img.src = objectUrl;
                preview.appendChild(img);
            } else if (file.type === 'application/pdf' || /\.pdf$/i.test(file.name)) {
                // Browsers that can't show PDFs inline fall back to the icon.
                var obj = document.createElement('object');
                obj.type = 'application/pdf';
                obj.data = objectUrl + '#toolbar=0&view=FitH';
                obj.innerHTML = '<div class="ufc-file"><i class="fas fa-file-pdf"></i>PDF document</div>';
                preview.appendChild(obj);
            } else {
                preview.innerHTML = '<div class="ufc-file"><i class="fas fa-file"></i>No preview available</div>';
            }

            // Inside a Bootstrap modal, live inside it so its focus trap stays happy.
            var openModal = document.querySelector('.modal.show');
            var host = openModal || home;
            if (box.parentNode !== host) host.appendChild(box);

            lastFocus = document.activeElement;
            box.hidden = false;
            (problem ? el('ufcCancel') : el('ufcConfirm')).focus();
            return new Promise(function (resolve) { resolver = resolve; });
        }

        document.addEventListener('DOMContentLoaded', function () {
            el('ufcConfirm').addEventListener('click', function () { finish(true); });
            el('ufcCancel').addEventListener('click', function () { finish(false); });
            // Esc cancels only this preview, not the form popup underneath.
            window.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && box && !box.hidden) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    finish(false);
                }
            }, true);
        });

        return { ask: ask };
    })();

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-multi-upload]').forEach(initMultiFileUpload);
    });

    function initMultiFileUpload(container) {
        var slotsContainer = container.querySelector('[data-multi-upload-slots]');
        var template = container.querySelector('template');
        var addBtn = container.querySelector('[data-multi-upload-add]');
        var label = (container.querySelector('.form-label') || {}).textContent || '';
        label = label.replace('*', '').replace(/\s+/g, ' ').trim();

        function addSlot(removable) {
            var fragment = template.content.cloneNode(true);
            var slot = fragment.querySelector('[data-multi-upload-slot]');
            var input = fragment.querySelector('[data-multi-upload-input]');
            var removeBtn = fragment.querySelector('[data-multi-upload-remove]');
            var prompt = fragment.querySelector('[data-multi-upload-prompt]');
            var fileRow = fragment.querySelector('[data-multi-upload-file]');
            var thumbEl = fragment.querySelector('[data-multi-upload-thumb]');
            var nameEl = fragment.querySelector('[data-multi-upload-name]');
            var sizeEl = fragment.querySelector('[data-multi-upload-size]');
            var clearBtn = fragment.querySelector('[data-multi-upload-clear]');
            var confirmedFile = null; // what this slot currently holds
            var thumbUrl = null;

            function humanSize(bytes) {
                return bytes >= 1024 * 1024 ? (bytes / (1024 * 1024)).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
            }

            // Show the chosen file in place of the upload prompt, or the prompt again when empty.
            function show(file) {
                if (thumbUrl) { URL.revokeObjectURL(thumbUrl); thumbUrl = null; }
                slot.classList.toggle('has-file', !!file);
                prompt.hidden = !!file;
                fileRow.hidden = !file;
                if (!file) return;
                thumbEl.innerHTML = '';
                if (/^image\//.test(file.type)) {
                    thumbUrl = URL.createObjectURL(file);
                    var img = new Image();
                    img.alt = '';
                    img.src = thumbUrl;
                    thumbEl.appendChild(img);
                } else {
                    thumbEl.innerHTML = '<i class="fas fa-file-pdf"></i>';
                }
                nameEl.textContent = file.name;
                nameEl.title = file.name;
                sizeEl.textContent = humanSize(file.size) + ' · Click to replace';
            }

            function setInputFile(file) {
                if (!file) { input.value = ''; return; }
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }

            // Ask before keeping a newly picked/dropped file; otherwise put
            // back what the slot held before.
            function offer(file) {
                if (!file) { setInputFile(confirmedFile); return; }
                uploadConfirm.ask(file, label).then(function (ok) {
                    if (ok) {
                        confirmedFile = file;
                        setInputFile(file);
                        show(file);
                    } else {
                        setInputFile(confirmedFile);
                        show(confirmedFile);
                    }
                });
            }

            if (removable) {
                removeBtn.classList.remove('d-none');
                removeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (thumbUrl) URL.revokeObjectURL(thumbUrl);
                    slot.remove();
                });
            }

            // Remove just the photo/file; the upload box stays for a new one.
            clearBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                confirmedFile = null;
                setInputFile(null);
                show(null);
            });

            slot.addEventListener('click', function (e) {
                if (e.target.closest('[data-multi-upload-remove], [data-multi-upload-clear]')) return;
                input.click();
            });
            input.addEventListener('change', function () {
                // Picking "Cancel" in the file dialog empties the input in some
                // browsers; keep the confirmed file in that case.
                offer(input.files[0] || null);
            });
            slot.addEventListener('dragover', function (e) { e.preventDefault(); slot.classList.add('bg-white', 'border-success'); });
            slot.addEventListener('dragleave', function (e) { e.preventDefault(); slot.classList.remove('bg-white', 'border-success'); });
            slot.addEventListener('drop', function (e) {
                e.preventDefault();
                slot.classList.remove('bg-white', 'border-success');
                if (e.dataTransfer.files.length) offer(e.dataTransfer.files[0]);
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
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 d-none" data-multi-upload-remove title="Remove this upload box" style="font-size: 0.65rem;"></button>
            <div data-multi-upload-prompt>
                <i class="fas fa-cloud-upload-alt text-muted mb-2 d-block"></i>
                <p class="fw-medium text-dark mb-1 small">Click to upload or drag and drop</p>
                <p class="text-muted small mb-0">PDF, JPG, or PNG (Max 10MB)</p>
            </div>
            <div class="mu-file" data-multi-upload-file hidden>
                <span class="mu-thumb" data-multi-upload-thumb></span>
                <span class="mu-info">
                    <span class="mu-name" data-multi-upload-name></span>
                    <span class="mu-sub" data-multi-upload-size></span>
                </span>
                <button type="button" class="btn btn-outline-danger btn-sm mu-remove" data-multi-upload-clear>
                    <i class="fas fa-trash-alt me-1"></i>Remove
                </button>
            </div>
            <input type="file" name="{{ $field }}[]" class="d-none" accept=".pdf,.jpg,.jpeg,.png" data-multi-upload-input>
        </div>
    </template>
</div>
