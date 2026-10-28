{{--
    Row actions: one clear button (the optional "primary" slot), everything
    else in "More". An action shown as the button never repeats inside More,
    and More is left out entirely when it would be empty.

    <x-action-dropdown>
        <x-slot:primary><button class="btn btn-outline-primary btn-sm" ...>View</button></x-slot:primary>
        <x-dropdown-item icon="fa-pen" ...>Edit</x-dropdown-item>
    </x-action-dropdown>
--}}
@props(['align' => 'end'])

@php
    $hasMenu = trim(preg_replace('/<!--.*?-->/s', '', $slot->toHtml())) !== '';
@endphp

<div class="row-actions">
    {{ $primary ?? '' }}
    @if($hasMenu)
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">More</button>
        <ul class="dropdown-menu dropdown-menu-{{ $align }} grouped-menu">
            {{ $slot }}
        </ul>
    </div>
    @endif
</div>
