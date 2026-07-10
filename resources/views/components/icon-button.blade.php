@props(['icon', 'color' => 'secondary', 'title' => ''])

<button type="button" title="{{ $title }}" {{ $attributes->merge(['class' => "icon-btn text-$color"]) }}>
    <i class="fas {{ $icon }}"></i>
</button>
