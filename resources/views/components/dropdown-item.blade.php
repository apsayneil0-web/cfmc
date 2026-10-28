{{-- One "More" menu entry. Icons stay quiet grey; only destructive entries
     (danger, or color="danger") are shown in clay. --}}
@props(['icon', 'color' => 'secondary', 'href' => '#', 'danger' => false])

@php $isDanger = $danger || $color === 'danger'; @endphp

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'dropdown-item' . ($isDanger ? ' text-danger' : '')]) }}>
        <i class="fas {{ $icon }} me-2 fa-fw {{ $isDanger ? 'text-danger' : 'text-gray-500' }}"></i>{{ $slot }}
    </a>
</li>
