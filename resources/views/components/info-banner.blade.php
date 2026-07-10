@props(['variant' => 'info', 'icon' => null, 'title' => null])

@php
    $icons = ['info' => 'fa-circle-info', 'success' => 'fa-circle-check', 'warning' => 'fa-triangle-exclamation', 'danger' => 'fa-circle-exclamation'];
    $resolvedIcon = $icon ?? $icons[$variant] ?? 'fa-circle-info';
@endphp

<div {{ $attributes->merge(['class' => "info-banner variant-$variant"]) }}>
    <i class="fas {{ $resolvedIcon }} mt-1"></i>
    <div>
        @if($title)
            <p class="fw-semibold mb-1">{{ $title }}</p>
        @endif
        <div class="small mb-0">{{ $slot }}</div>
    </div>
</div>
