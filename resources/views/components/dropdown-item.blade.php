@props(['icon', 'color' => 'secondary', 'href' => '#', 'danger' => false])

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'dropdown-item' . ($danger ? ' text-danger' : '')]) }}>
        <i class="fas {{ $icon }} me-2 fa-fw {{ $danger ? 'text-danger' : "text-$color" }}"></i>{{ $slot }}
    </a>
</li>
