{{-- The cooperative's delinquency ladder, as applied by Loan::applyGracePeriodPolicy(). --}}
@php
    $steps = [
        ['tone' => 'warning', 'when' => 'Due date missed', 'what' => '2% grace interest', 'detail' => 'Added once. The farmer has a 30-day grace period.'],
        ['tone' => 'danger', 'when' => 'After 30 days', 'what' => '10% penalty and restricted', 'detail' => 'Farmer cannot request a new loan until fully paid.'],
        ['tone' => 'danger', 'when' => '5 months unpaid', 'what' => 'Barangay summons', 'detail' => 'Flagged and the Administrator is notified.'],
        ['tone' => 'deep', 'when' => '6 months unpaid', 'what' => 'Legal action', 'detail' => 'Flagged and the Administrator is notified.'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'section-card p-4 p-md-5']) }}>
    <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-4">
        <h3 class="text-xl font-semibold text-gray-900 mb-0">Loan rules</h3>
        <p class="text-sm text-gray-500 mb-0">Interest: 2% is added to the balance every due date that passes without payment.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($steps as $step)
        <div>
            <div class="loan-rule-track">
                <span class="loan-rule-dot is-{{ $step['tone'] }}"></span>
            </div>
            <p class="text-xs fw-semibold text-gray-600 text-uppercase mb-1" style="letter-spacing: 0.06em;">{{ $step['when'] }}</p>
            <p class="text-sm fw-semibold text-gray-900 mb-1">{{ $step['what'] }}</p>
            <p class="text-sm text-gray-500 mb-0">{{ $step['detail'] }}</p>
        </div>
        @endforeach
    </div>
</div>
