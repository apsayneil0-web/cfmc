@props(['id', 'existingLabel' => null, 'parentModalId' => null, 'existingUrl' => null])

@once
<style>
    .collateral-viewfinder {
        position: relative;
        background: #000;
        overflow: hidden;
    }
    .collateral-corner {
        position: absolute;
        width: 32px;
        height: 32px;
        border-color: #fff;
        pointer-events: none;
    }
    .collateral-corner-tl { top: 14px; left: 14px; border-top: 3px solid; border-left: 3px solid; }
    .collateral-corner-tr { top: 14px; right: 14px; border-top: 3px solid; border-right: 3px solid; }
    .collateral-corner-bl { bottom: 14px; left: 14px; border-bottom: 3px solid; border-left: 3px solid; }
    .collateral-corner-br { bottom: 14px; right: 14px; border-bottom: 3px solid; border-right: 3px solid; }
    .collateral-close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .collateral-shutter-btn {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid #198754;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .collateral-shutter-btn-dot {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #198754;
    }
    .collateral-round-btn {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
</style>

<script>
    // In-page camera capture for Collateral Proof: opens a live camera
    // preview in a modal (via getUserMedia), lets the user snap a photo,
    // review it, then syncs it into the hidden "documents" file input via
    // the DataTransfer API — same submitted field either way, whether the
    // photo came from the live camera or the plain file chooser.
    window.__collateralCam = window.__collateralCam || {};

    async function collateralOpenCamera(id) {
        var video = document.getElementById(id + '_video');
        var img = document.getElementById(id + '_preview');
        var shutterBtn = document.getElementById(id + '_captureBtn');
        var retakeBtn = document.getElementById(id + '_retakeBtn');
        var useBtn = document.getElementById(id + '_useBtn');
        var errorEl = document.getElementById(id + '_error');

        video.classList.remove('d-none');
        img.classList.add('d-none');
        shutterBtn.classList.remove('d-none');
        retakeBtn.classList.add('d-none');
        useBtn.classList.add('d-none');
        errorEl.classList.add('d-none');

        try {
            var stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            window.__collateralCam[id] = { stream: stream };
            video.srcObject = stream;
            await video.play();
        } catch (err) {
            errorEl.textContent = 'Could not open the camera (' + err.message + '). Check camera permissions, or use "Choose File" instead.';
            errorEl.classList.remove('d-none');
            shutterBtn.classList.add('d-none');
        }
    }

    function collateralCapturePhoto(id) {
        var video = document.getElementById(id + '_video');
        var canvas = document.getElementById(id + '_canvas');
        var img = document.getElementById(id + '_preview');
        var shutterBtn = document.getElementById(id + '_captureBtn');
        var retakeBtn = document.getElementById(id + '_retakeBtn');
        var useBtn = document.getElementById(id + '_useBtn');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function (blob) {
            window.__collateralCam[id].blob = blob;
            img.src = URL.createObjectURL(blob);
        }, 'image/jpeg', 0.9);

        video.classList.add('d-none');
        img.classList.remove('d-none');
        shutterBtn.classList.add('d-none');
        retakeBtn.classList.remove('d-none');
        useBtn.classList.remove('d-none');
    }

    function collateralRetakePhoto(id) {
        collateralOpenCamera(id);
    }

    function collateralUsePhoto(id) {
        var target = document.getElementById(id);
        var label = document.getElementById(id + '_label');
        var blob = window.__collateralCam[id]?.blob;

        if (!blob) {
            return;
        }

        var file = new File([blob], 'collateral-photo-' + Date.now() + '.jpg', { type: 'image/jpeg' });
        var dt = new DataTransfer();
        dt.items.add(file);
        target.files = dt.files;
        label.textContent = file.name;
        label.classList.add('text-success');
        collateralShowPreview(id, file);

        collateralCloseCamera(id);
    }

    // Points the small "View Photo" link at whatever file is currently
    // selected (camera capture or plain file choice) and reveals it.
    function collateralShowPreview(id, file) {
        var viewLink = document.getElementById(id + '_viewLink');
        viewLink.href = URL.createObjectURL(file);
        viewLink.classList.remove('d-none');
    }

    // Closes the camera modal and, if it was opened from another modal (the
    // New Appointment / Reschedule form), brings that form back into view
    // instead of leaving the farmer with nothing open — the form's fields
    // were never actually removed, just hidden, so everything they'd
    // already typed is still there when it reappears.
    function collateralCloseCamera(id) {
        collateralStopCamera(id);

        var modalEl = document.getElementById(id + '_cameraModal');
        var parentModalId = modalEl.dataset.parentModalId;

        if (parentModalId) {
            switchModal(id + '_cameraModal', parentModalId);
        } else {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
    }

    function collateralStopCamera(id) {
        var cam = window.__collateralCam[id];
        if (cam && cam.stream) {
            cam.stream.getTracks().forEach(function (track) { track.stop(); });
        }
    }

    // Choosing a file the normal way also needs to update the same label.
    function syncCollateralProofFile(sourceId, targetId, labelId) {
        var source = document.getElementById(sourceId);
        var target = document.getElementById(targetId);
        var label = document.getElementById(labelId);

        if (source.files.length > 0) {
            var file = source.files[0];
            var dt = new DataTransfer();
            dt.items.add(file);
            target.files = dt.files;
            label.textContent = file.name;
            label.classList.add('text-success');
            collateralShowPreview(targetId, file);
        }
    }

    // Starts the camera once the modal is actually visible (not on click),
    // so it works the same whether the modal opened directly or waited for
    // a parent modal to close first via switchModal().
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id.endsWith('_cameraModal')) {
            collateralOpenCamera(event.target.id.replace('_cameraModal', ''));
        }
    });

    document.addEventListener('hidden.bs.modal', function (event) {
        if (event.target.id.endsWith('_cameraModal')) {
            collateralStopCamera(event.target.id.replace('_cameraModal', ''));
        }

        // Safety net: if two modals ever end up open at once (e.g. a camera
        // button triggered without going through switchModal first), Bootstrap
        // can leave a stray dark backdrop behind with no modal visible on top
        // of it once both close. If nothing is actually open anymore but a
        // backdrop is still in the DOM, clear it so the page isn't stuck.
        setTimeout(function () {
            if (!document.querySelector('.modal.show') && document.querySelector('.modal-backdrop')) {
                document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }, 350);
    });

    // Hides one modal and, once it's fully closed, opens another — Bootstrap
    // doesn't support two open modals cleanly, so a camera button sitting
    // inside another modal (e.g. the Reschedule modal) needs its parent to
    // finish closing first.
    if (typeof switchModal !== 'function') {
        window.switchModal = function (fromModalId, toModalId) {
            var fromEl = document.getElementById(fromModalId);
            var fromModal = bootstrap.Modal.getInstance(fromEl);
            var openTarget = function () {
                new bootstrap.Modal(document.getElementById(toModalId)).show();
            };

            if (fromModal) {
                fromEl.addEventListener('hidden.bs.modal', openTarget, { once: true });
                fromModal.hide();
            } else {
                openTarget();
            }
        };
    }
</script>
@endonce

<div class="d-flex gap-2 mb-2">
    @if($parentModalId)
    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="switchModal('{{ $parentModalId }}', '{{ $id }}_cameraModal')">
        <i class="fas fa-camera me-1"></i> Take Photo
    </button>
    @else
    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#{{ $id }}_cameraModal">
        <i class="fas fa-camera me-1"></i> Take Photo
    </button>
    @endif
    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="document.getElementById('{{ $id }}_file').click()">
        <i class="fas fa-folder-open me-1"></i> Choose File
    </button>
</div>
<div class="d-flex align-items-center gap-2">
    <p class="small text-muted mb-0" id="{{ $id }}_label">{{ $existingLabel ?? 'No file chosen' }}</p>
    <a href="{{ $existingUrl }}" target="_blank" id="{{ $id }}_viewLink" class="btn btn-link btn-sm p-0 {{ $existingUrl ? '' : 'd-none' }}">View Photo</a>
</div>

<input type="file" name="documents" id="{{ $id }}" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
<input type="file" id="{{ $id }}_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png" onchange="syncCollateralProofFile('{{ $id }}_file', '{{ $id }}', '{{ $id }}_label')">

{{--
    Pushed to a stack rendered at the bottom of the page (outside this
    component's call site) instead of left inline here. This component is
    often used inside another modal (e.g. New Appointment/Reschedule), and a
    modal nested inside another modal's DOM is invisible once its parent
    gets display:none on close — Bootstrap's own .show class on the nested
    modal can't override a hidden ancestor. Pushing it to the page's own
    top-level stack keeps it a sibling of every other modal instead.
--}}
@push('modals')
<!-- Camera Capture Modal: styled like a native document scanner — full-bleed
     viewfinder with corner alignment guides and one big shutter button,
     instead of a typical dialog with a header bar and a row of buttons. -->
<div class="modal fade" id="{{ $id }}_cameraModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-parent-modal-id="{{ $parentModalId }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden">
            <div class="collateral-viewfinder">
                <button type="button" class="collateral-close-btn" aria-label="Close" onclick="collateralCloseCamera('{{ $id }}')">
                    <i class="fas fa-times"></i>
                </button>
                <p class="text-danger small m-3 d-none position-relative" style="z-index: 2;" id="{{ $id }}_error"></p>
                <video id="{{ $id }}_video" class="w-100 d-block" autoplay playsinline muted style="max-height: 70vh;"></video>
                <img id="{{ $id }}_preview" class="w-100 d-none" style="max-height: 70vh; object-fit: contain;">
                <span class="collateral-corner collateral-corner-tl"></span>
                <span class="collateral-corner collateral-corner-tr"></span>
                <span class="collateral-corner collateral-corner-bl"></span>
                <span class="collateral-corner collateral-corner-br"></span>
                <canvas id="{{ $id }}_canvas" class="d-none"></canvas>
            </div>
            <div class="bg-white py-4 d-flex align-items-center justify-content-center gap-4">
                <button type="button" class="collateral-shutter-btn" id="{{ $id }}_captureBtn" onclick="collateralCapturePhoto('{{ $id }}')" title="Capture">
                    <span class="collateral-shutter-btn-dot"></span>
                </button>
                <button type="button" class="collateral-round-btn btn btn-outline-secondary d-none" id="{{ $id }}_retakeBtn" onclick="collateralRetakePhoto('{{ $id }}')" title="Retake">
                    <i class="fas fa-redo"></i>
                </button>
                <button type="button" class="collateral-round-btn btn btn-success d-none" id="{{ $id }}_useBtn" onclick="collateralUsePhoto('{{ $id }}')" title="Use Photo">
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
