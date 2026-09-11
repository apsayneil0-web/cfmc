@props(['id', 'existingLabel' => null, 'parentModalId' => null])

@once
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
        var captureBtn = document.getElementById(id + '_captureBtn');
        var retakeBtn = document.getElementById(id + '_retakeBtn');
        var useBtn = document.getElementById(id + '_useBtn');
        var errorEl = document.getElementById(id + '_error');

        video.classList.remove('d-none');
        img.classList.add('d-none');
        captureBtn.classList.remove('d-none');
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
            captureBtn.classList.add('d-none');
        }
    }

    function collateralCapturePhoto(id) {
        var video = document.getElementById(id + '_video');
        var canvas = document.getElementById(id + '_canvas');
        var img = document.getElementById(id + '_preview');
        var captureBtn = document.getElementById(id + '_captureBtn');
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
        captureBtn.classList.add('d-none');
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

        collateralStopCamera(id);
        bootstrap.Modal.getInstance(document.getElementById(id + '_cameraModal')).hide();
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
            var dt = new DataTransfer();
            dt.items.add(source.files[0]);
            target.files = dt.files;
            label.textContent = source.files[0].name;
            label.classList.add('text-success');
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
<p class="small text-muted mb-0" id="{{ $id }}_label">{{ $existingLabel ?? 'No file chosen' }}</p>

<input type="file" name="documents" id="{{ $id }}" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
<input type="file" id="{{ $id }}_file" class="d-none" accept=".pdf,.jpg,.jpeg,.png" onchange="syncCollateralProofFile('{{ $id }}_file', '{{ $id }}', '{{ $id }}_label')">

<!-- Camera Capture Modal -->
<div class="modal fade" id="{{ $id }}_cameraModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-camera me-2"></i>Take Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="collateralStopCamera('{{ $id }}')"></button>
            </div>
            <div class="modal-body p-0 bg-black text-center">
                <p class="text-danger small m-3 d-none" id="{{ $id }}_error"></p>
                <video id="{{ $id }}_video" class="w-100" autoplay playsinline muted style="max-height: 70vh;"></video>
                <img id="{{ $id }}_preview" class="w-100 d-none" style="max-height: 70vh; object-fit: contain;">
                <canvas id="{{ $id }}_canvas" class="d-none"></canvas>
            </div>
            <div class="modal-footer bg-light justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="collateralStopCamera('{{ $id }}')">Cancel</button>
                <button type="button" class="btn btn-primary" id="{{ $id }}_captureBtn" onclick="collateralCapturePhoto('{{ $id }}')">
                    <i class="fas fa-camera me-1"></i> Capture
                </button>
                <button type="button" class="btn btn-outline-secondary d-none" id="{{ $id }}_retakeBtn" onclick="collateralRetakePhoto('{{ $id }}')">
                    <i class="fas fa-redo me-1"></i> Retake
                </button>
                <button type="button" class="btn btn-success d-none" id="{{ $id }}_useBtn" onclick="collateralUsePhoto('{{ $id }}')">
                    <i class="fas fa-check me-1"></i> Use Photo
                </button>
            </div>
        </div>
    </div>
</div>
