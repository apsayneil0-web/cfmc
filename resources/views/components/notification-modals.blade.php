{{--
    Rendered outside the topbar on purpose: the topbar uses backdrop-filter,
    which (like transform) makes an element the containing block for its
    position:fixed descendants. A Bootstrap modal nested inside it would be
    confined to the topbar's small box instead of centered on the viewport.
--}}
@props(['notifications' => collect()])

@foreach($notifications as $notification)
@php
    $announcement = $notification->announcement;
    $display = \App\Models\Notification::typeDisplay($notification->type);
@endphp
@if($announcement)
<div class="modal fade" id="notifDetail{{ $notification->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">{{ $display['icon'] }} {{ $announcement->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $announcement->purpose }}</p></div>
                    <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $announcement->announcement_date?->format('M d, Y') ?? '—' }}</p></div>
                    @if($announcement->time)
                    <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">🕘 {{ \Carbon\Carbon::parse($announcement->time)->format('g:i A') }}</p></div>
                    @endif
                    @if($announcement->location)
                    <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">📍 {{ $announcement->location }}</p></div>
                    @endif
                    @if($announcement->description)
                    <div class="col-12"><label class="text-muted small d-block">Description</label><p class="fw-medium mb-0">{{ $announcement->description }}</p></div>
                    @endif
                    @if($announcement->resolution)
                    <div class="col-12"><label class="text-muted small d-block">Resolution</label><p class="fw-medium mb-0">{{ $announcement->resolution }}</p></div>
                    @endif
                    <div class="col-6"><label class="text-muted small d-block">Recipients</label><p class="fw-medium mb-0">{{ $announcement->audience_label }}</p></div>
                    <div class="col-6"><label class="text-muted small d-block">Posted By</label><p class="fw-medium mb-0">{{ $announcement->creator->name ?? '—' }}</p></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <span class="text-muted small me-auto">Posted {{ $announcement->created_at->format('M d, Y \a\t g:i A') }}</span>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach
