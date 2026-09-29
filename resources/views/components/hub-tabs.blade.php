@props(['label', 'tabs'])

{{-- Tab strip for a sidebar hub. Each tab: route, label, icon, optional params, count, and active (overrides the route match). --}}
<nav class="hub-tabs mb-4" aria-label="{{ $label }}">
    @foreach($tabs as $tab)
        @php $isActive = $tab['active'] ?? request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route'], $tab['params'] ?? []) }}" class="hub-tab {{ $isActive ? 'active' : '' }}" @if($isActive) aria-current="page" @endif>
            <i class="{{ $tab['icon'] }}"></i>
            <span>{{ $tab['label'] }}</span>
            @isset($tab['count'])
                <span class="hub-tab-count">{{ number_format($tab['count']) }}</span>
            @endisset
        </a>
    @endforeach
</nav>
