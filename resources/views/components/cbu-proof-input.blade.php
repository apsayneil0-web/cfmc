@props([
    'id',
    'required' => true,
    'existingUrl' => null,
    'label' => 'Proof of Payment',
    'hint' => 'Photo of the receipt or deposit slip · JPG, PNG or PDF · up to 5 MB',
    'missingMessage' => 'Attach the proof of payment (receipt photo or deposit slip) before recording.',
])

{{--
    Proof upload (receipt photo, deposit slip, PDF). Used for CBU payments
    and, with its own label/hint/missing-message, for recorded expenses.
    Submits as "proof_document". Checks type/size in the browser before
    submit so the Manager doesn't lose the form to a server-side rejection;
    the server re-validates the same rules (PaymentController::recordCbuPayment).
    The "View" link opens the picked file in the shared <x-file-viewer />.
--}}
@once
<script>
    function cbuProofChanged(input) {
        var id = input.id;
        var error = document.getElementById(id + '_error');
        var viewLink = document.getElementById(id + '_viewLink');
        var removeBtn = document.getElementById(id + '_remove');
        var file = input.files[0];
        var message = '';

        if (file) {
            var okTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (okTypes.indexOf(file.type) === -1) {
                message = '"' + file.name + '" isn\'t accepted. Upload a JPG, PNG or PDF.';
            } else if (file.size > 5 * 1024 * 1024) {
                message = '"' + file.name + '" is ' + (file.size / 1048576).toFixed(1) + ' MB. The limit is 5 MB — take a smaller photo or compress it.';
            }
        }

        if (message) {
            input.value = '';
            file = null;
        }

        error.textContent = message;
        error.classList.toggle('d-none', !message);
        input.classList.toggle('is-invalid', !!message);

        if (file) {
            viewLink.href = URL.createObjectURL(file);
            viewLink.setAttribute('data-viewer-type', file.type);
            viewLink.classList.remove('d-none');
            removeBtn.classList.remove('d-none');
        } else if (!viewLink.dataset.existing) {
            viewLink.classList.add('d-none');
            removeBtn.classList.add('d-none');
        } else {
            removeBtn.classList.add('d-none');
        }
    }

    function cbuProofRemove(id) {
        var input = document.getElementById(id);
        input.value = '';
        var viewLink = document.getElementById(id + '_viewLink');
        if (viewLink.dataset.existing) {
            viewLink.href = viewLink.dataset.existing;
            viewLink.removeAttribute('data-viewer-type');
        }
        cbuProofChanged(input);
    }

    // Native "required" already blocks an empty submit; this adds a clear
    // message next to the field instead of only the browser's tooltip.
    document.addEventListener('invalid', function (event) {
        if (event.target.matches && event.target.matches('[data-cbu-proof]')) {
            var error = document.getElementById(event.target.id + '_error');
            error.textContent = event.target.dataset.missingMessage;
            error.classList.remove('d-none');
            event.target.classList.add('is-invalid');
        }
    }, true);
</script>
@endonce

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    <label class="form-label fw-semibold" for="{{ $id }}">
        {{ $label }}
        @if($required)<span class="text-danger">*</span>@else<span class="text-muted fw-normal small">(optional — replaces the current file)</span>@endif
    </label>
    <input type="file" name="proof_document" id="{{ $id }}" class="form-control" data-cbu-proof data-missing-message="{{ $missingMessage }}"
           accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
           onchange="cbuProofChanged(this)" @if($required) required @endif>
    <div class="d-flex align-items-center gap-3 mt-1">
        <p class="text-muted small mb-0 me-auto">{{ $hint }}</p>
        <a href="{{ $existingUrl ?? '#' }}" target="_blank" data-file-viewer data-viewer-title="{{ $label }}"
           id="{{ $id }}_viewLink" @if($existingUrl) data-existing="{{ $existingUrl }}" @endif
           class="btn btn-link btn-sm p-0 text-nowrap {{ $existingUrl ? '' : 'd-none' }}"><i class="fas fa-eye me-1"></i>View</a>
        <button type="button" id="{{ $id }}_remove" class="btn btn-link btn-sm p-0 text-danger text-nowrap d-none" onclick="cbuProofRemove('{{ $id }}')">Remove</button>
    </div>
    <p class="small text-danger mb-0 mt-1 d-none" id="{{ $id }}_error"></p>
</div>
