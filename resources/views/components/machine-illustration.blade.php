@props(['type' => null])

@php
    // Pick a line drawing from the free-text machine type; anything unknown
    // falls back to a generic gear.
    $t = strtolower($type ?? '');
    $kind = match (true) {
        str_contains($t, 'truck') => 'truck',
        str_contains($t, 'seed') || str_contains($t, 'plant') => 'seeder',
        str_contains($t, 'harvest') || str_contains($t, 'thresh') => 'harvester',
        str_contains($t, 'tractor') || str_contains($t, 'tiller') || str_contains($t, 'plow') => 'tractor',
        default => 'other',
    };
@endphp

<svg {{ $attributes->merge(['class' => 'machine-illustration machine-illustration-'.$kind]) }} viewBox="0 0 120 80" fill="none" stroke="currentColor"
    stroke-width="4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($kind)
        @case('truck')
            <path d="M8 56V20h56v36M64 32h20l14 14v10h-6M64 56h8" />
            <path d="M28 56h16" />
            <circle cx="18" cy="60" r="8" />
            <circle cx="82" cy="60" r="8" />
            @break
        @case('seeder')
            <path d="M38 12h44l-10 22H48z" />
            <path d="M60 34v8M18 42h84M30 42v18M46 42v18M60 42v18M74 42v18M90 42v18" />
            @break
        @case('harvester')
            <path d="M12 50V28h30l8-12h22v34M72 30h22l14 20" />
            <path d="M94 50h14M8 50h100" />
            <circle cx="28" cy="60" r="9" />
            <circle cx="82" cy="60" r="7" />
            @break
        @case('tractor')
            <path d="M34 34V14h28l8 20h18l6 14" />
            <path d="M78 34v-8" />
            <circle cx="38" cy="54" r="18" />
            <circle cx="92" cy="60" r="10" />
            <path d="M56 54h26" />
            @break
        @default
            <circle cx="60" cy="40" r="12" />
            <path d="M60 14v8M60 58v8M34 40h8M78 40h8M42 22l6 6M72 52l6 6M78 22l-6 6M48 52l-6 6" />
    @endswitch
</svg>
