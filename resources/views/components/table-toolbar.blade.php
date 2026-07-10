@props(['title' => null])

<div {{ $attributes->merge(['class' => 'table-toolbar']) }}>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
            @isset($filters)
                {{ $filters }}
            @endisset
        </div>
        @isset($actions)
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
