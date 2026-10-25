{{--
    GCash-style installment timeline, shared between a farmer's active loan
    and their paid-off loan history so an early payoff (which closes every
    remaining installment at once, waiving future interest) shows the exact
    same "all paid" schedule either way.

    <x-loan-repayment-schedule :loan="$activeLoan" />

    Pass :padded="false" when embedding inside something that already has its
    own padding (e.g. an x-modal body), to avoid double-indenting it.
--}}
@props(['loan', 'padded' => true])

@php $pad = $padded ? 'px-4 px-md-6' : ''; @endphp

@if(!empty($loan->installment_schedule))
<h4 class="text-sm fw-semibold text-dark {{ $pad }} mb-3">Repayment Schedule</h4>

@if($loan->paid_off_early)
<div class="{{ $pad }} mb-3">
    <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert">
        <i class="fas fa-flag-checkered"></i>
        <span>Paid off early &mdash; the remaining months' interest was waived.</span>
    </div>
</div>
@elseif($loan->status === 'fully_paid')
<div class="{{ $pad }} mb-3">
    <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert">
        <i class="fas fa-check-circle"></i>
        <span>Loan fully paid &mdash; nothing more is owed.</span>
    </div>
</div>
@endif

<div class="{{ $pad }} mb-4">
    <div class="loan-timeline">
        @foreach($loan->installment_schedule as $row)
        @if($row->status === 'collapsed')
        <div class="timeline-row">
            <div class="timeline-marker timeline-marker-paid">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div class="timeline-content">
                <div class="fw-semibold text-dark">Installments {{ $row->from }}&ndash;{{ $row->to }} &middot; loan complete</div>
                <div class="small text-muted">Closed together by the payment above &mdash; no further due dates.</div>
            </div>
        </div>
        @else
        <div class="timeline-row">
            <div class="timeline-marker timeline-marker-{{ $row->status }}">
                @if($row->status === 'paid')
                <i class="fas fa-check"></i>
                @endif
            </div>
            <div class="timeline-content">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold text-dark">
                            {{ $row->due_date->format('M d, Y') }}
                            @if($row->status === 'late')
                            <span class="badge bg-danger ms-1">LATE</span>
                            @endif
                        </div>
                        <div class="small text-muted">{{ $row->number }} of {{ $loan->effective_term_months }}</div>
                        @if(($row->closing ?? false) && $row->closing_type)
                        <div class="small text-primary fst-italic">{{ ucfirst($row->closing_type) }} total &mdash; paid in full this month</div>
                        @endif
                    </div>
                    <div class="text-end">
                        @if($row->status === 'paid')
                        <div><span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span></div>
                        @elseif($row->status === 'current' && (float) $loan->current_period_paid > 0)
                        <div><span class="badge bg-info-subtle text-info border border-info-subtle">Partial &ndash; {{ peso($loan->current_period_paid) }} paid</span></div>
                        @endif
                        <div class="fw-semibold {{ $row->status === 'paid' ? 'text-muted text-decoration-line-through' : 'text-primary' }}">{{ peso($row->amount) }}</div>
                        @unless($row->closing ?? false)
                        <button type="button" class="btn btn-sm btn-link p-0 text-muted text-decoration-none installment-toggle" data-target="installmentDetail{{ $loan->id }}-{{ $row->number }}" aria-expanded="false">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        @endunless
                    </div>
                </div>
                @unless($row->closing ?? false)
                <div class="small text-muted mt-1 d-none" id="installmentDetail{{ $loan->id }}-{{ $row->number }}">
                    Principal: {{ peso($loan->installment_amount) }} &bull; Interest: {{ peso($loan->monthly_due - $loan->installment_amount) }}
                    @if($row->status === 'current' && $loan->carried_over_amount > 0)
                    <br>Includes {{ peso($loan->carried_over_amount) }} carried over from a previous partial payment.
                    @endif
                </div>
                @endunless
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <p class="text-muted small mb-0 mt-2">
        <i class="fas fa-info-circle me-1"></i>
        @if($loan->paid_off_early)
        This loan was paid off ahead of schedule.
        @elseif($loan->status === 'fully_paid')
        This loan is fully paid. There is no remaining or projected schedule.
        @else
        This is a projected schedule based on your current due date. Payments are recorded by your CFMC Manager — visit or contact the cooperative office to make a payment.
        @endif
    </p>
</div>
@endif
