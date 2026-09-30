@props(['align' => 'end'])

<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-{{ $align }}">
        {{ $slot }}
    </ul>
</div>
