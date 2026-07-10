@props(['name' => '', 'color' => 'primary', 'size' => 10])

@php
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(collect($parts)->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode(''));
@endphp

<div {{ $attributes->merge(['class' => "avatar-initials text-$color"]) }} style="width: {{ $size / 4 }}rem; height: {{ $size / 4 }}rem; font-size: {{ $size >= 12 ? '1.1rem' : '0.85rem' }};">
    {{ $initials ?: '?' }}
</div>
