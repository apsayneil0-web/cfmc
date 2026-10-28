@props(['title', 'steps' => []])

{{-- "How a loan moves  ① Farmer requests → ② ..." explainer strip. --}}
<div {{ $attributes->merge(['class' => 'flow-strip']) }}>
    <span class="flow-strip-title">{{ $title }}</span>
    @foreach($steps as $step)
    <span class="flow-strip-step">
        <span class="flow-strip-num">{{ $loop->iteration }}</span>{{ $step }}
        @unless($loop->last)<span class="flow-strip-arrow" aria-hidden="true">&rarr;</span>@endunless
    </span>
    @endforeach
</div>
