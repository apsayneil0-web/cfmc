@props(['id', 'title' => '', 'size' => '', 'staticBackdrop' => false])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true"
    @if($staticBackdrop) data-bs-backdrop="static" @endif>
    <div class="modal-dialog modal-dialog-centered {{ $size }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
