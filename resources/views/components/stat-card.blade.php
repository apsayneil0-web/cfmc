@props([
    'label',
    'value',
    'icon' => 'fa-chart-line',
    'color' => 'primary',
    'trend' => null,
    'trendDirection' => 'up',
])

<div {{ $attributes->merge(['class' => 'stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6']) }}>
    <div class="d-flex align-items-center gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <p class="text-sm text-gray-500 mb-1">{{ $label }}</p>
            <p class="text-xl font-bold text-gray-900 mb-0" style="overflow-wrap: break-word;">{{ $value }}</p>
            @if($trend)
                <p class="text-xs mt-2 mb-0 d-flex align-items-center gap-1 {{ $trendDirection === 'down' ? 'text-danger' : 'text-success' }}">
                    <i class="fas {{ $trendDirection === 'down' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                    {{ $trend }}
                </p>
            @endif
        </div>
        <div class="stat-icon text-{{ $color }}" style="flex-shrink: 0;">
            <i class="fas {{ $icon }}"></i>
        </div>
    </div>
</div>
