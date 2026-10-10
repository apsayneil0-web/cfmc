{{--
    "View Archived (n)" / "Back to Active" switch for list pages, matching
    Machinery. Keeps the current search/filters when switching.

    <x-archive-toggle route="manager.membership" :showing="$showArchived" :count="$archivedCount" />

    `except` lists query keys to drop when switching (e.g. a status filter
    that only applies to one of the two views, or pagination).
--}}
@props(['route', 'showing' => false, 'count' => null, 'except' => ['status', 'page']])

@php
    $base = collect(request()->query())->except(array_merge(['archived'], $except))->all();
@endphp

@if($showing)
    <a href="{{ route($route, $base) }}" {{ $attributes->merge(['class' => 'btn btn-outline-secondary d-inline-flex align-items-center gap-2 text-nowrap']) }}>
        <i class="fas fa-arrow-left"></i><span>Back to Active</span>
    </a>
@else
    <a href="{{ route($route, array_merge($base, ['archived' => 1])) }}" {{ $attributes->merge(['class' => 'btn btn-outline-secondary d-inline-flex align-items-center gap-2 text-nowrap']) }}>
        <i class="fas fa-box-archive"></i>
        <span>View Archived</span>
        @if(!is_null($count))
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">{{ number_format($count) }}</span>
        @endif
    </a>
@endif
