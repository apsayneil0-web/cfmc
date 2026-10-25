<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    /**
     * Days after the original due date during which the partial-payment (2%)
     * policy applies before escalating to the full grace-period (10%) penalty.
     */
    private const GRACE_PERIOD_DAYS = 30;

    private const PARTIAL_PENALTY_RATE = 2.0;

    private const GRACE_PENALTY_RATE = 10.0;

    private const BARANGAY_SUMMON_MONTHS = 5;

    private const LEGAL_ACTION_MONTHS = 6;

    protected $fillable = [
        'loan_request_id',
        'principal_amount',
        'interest_rate',
        'repayment_terms_months',
        'installment_amount',
        'collateral',
        'remaining_balance',
        'current_period_paid',
        'carried_over_amount',
        'next_due_date',
        'original_due_date',
        'partial_penalty_applied_at',
        'grace_penalty_applied_at',
        'barangay_summon_at',
        'legal_action_at',
        'status',
        'notes',
        'archived_at',
        'disbursed_at',
        'disbursement_method',
        'reference_no',
        'disbursed_by',
        'scheduled_disbursement_date',
        'scheduled_by',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
            'original_due_date' => 'date',
            'partial_penalty_applied_at' => 'datetime',
            'grace_penalty_applied_at' => 'datetime',
            'barangay_summon_at' => 'datetime',
            'legal_action_at' => 'datetime',
            'archived_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'scheduled_disbursement_date' => 'date',
        ];
    }

    /**
     * Monthly Installment = Minimum Due + Principal Monthly Payment, where:
     *   Minimum Due = Loan Amount x interest rate (e.g. 30,000 x 2% = 600)
     *   Principal Monthly Payment = Loan Amount / Loan Term (installment_amount)
     * e.g. principal 30,000, term 12, rate 2%: 600 + 2,500 = 3,100.
     */
    public function getMonthlyDueAttribute(): float
    {
        $minimumDue = round((float) $this->principal_amount * ((float) $this->interest_rate / 100), 2);

        return round($minimumDue + (float) $this->installment_amount, 2);
    }

    /**
     * What's actually due for the upcoming period: the standard monthly_due,
     * plus any shortfall a "partial" payment carried forward from a prior
     * period (carried_over_amount — see recordPayment()), minus whatever
     * progress has already been applied toward the current period
     * (current_period_paid) — including a prepayment that overshot the
     * amount due, which leaves its excess sitting there as a head-start
     * credit on the next period instead of just being stranded unseen.
     */
    public function getAmountDueAttribute(): float
    {
        return max(0, round($this->monthly_due + (float) $this->carried_over_amount - (float) $this->current_period_paid, 2));
    }

    /**
     * Which installment number next_due_date currently sits at, counting
     * from original_due_date as #1. Null if the loan hasn't been disbursed
     * yet. applyOverdueInterest() keeps advancing next_due_date for as long
     * as a loan stays unpaid, with no ceiling at repayment_terms_months, so
     * this can legitimately exceed the original term count once a farmer
     * runs past their last scheduled month while still owing a balance.
     */
    public function getCurrentInstallmentNumberAttribute(): ?int
    {
        if (! $this->original_due_date || ! $this->next_due_date) {
            return null;
        }

        // diffInMonths returns a float with sub-millisecond drift (e.g.
        // 3.000000000002987 instead of 3) rather than a clean int, so round
        // before comparing — otherwise every === below silently fails.
        return (int) round($this->original_due_date->diffInMonths($this->next_due_date)) + 1;
    }

    /**
     * The term this loan is effectively running on: the originally agreed
     * repayment_terms_months, unless the farmer has run past it while still
     * owing a balance, in which case it grows to match — automatically
     * "extending" the loan by however many extra months it's actually
     * taking, without overwriting the original contracted term.
     */
    public function getEffectiveTermMonthsAttribute(): int
    {
        return max($this->repayment_terms_months, $this->current_installment_number ?? 0);
    }

    /**
     * A projected installment-by-installment repayment schedule (GCash-style
     * "8 of 12" timeline), one row per effective_term_months, computed from
     * original_due_date/next_due_date rather than stored — this app only
     * tracks a single running balance and one next_due_date, not a
     * persisted per-installment ledger. Installments before the current
     * next_due_date are assumed settled; the one at next_due_date is
     * "late" if the loan has gone overdue, otherwise "current"; the rest
     * are "upcoming". A fully paid loan is handled entirely differently —
     * see fullyPaidInstallmentSchedule().
     */
    public function getInstallmentScheduleAttribute(): array
    {
        $currentNumber = $this->current_installment_number;

        if ($currentNumber === null) {
            return [];
        }

        if ($this->status === 'fully_paid') {
            return $this->fullyPaidInstallmentSchedule($currentNumber);
        }

        $schedule = [];

        for ($number = 1; $number <= $this->effective_term_months; $number++) {
            $status = match (true) {
                $number < $currentNumber => 'paid',
                $number === $currentNumber && $this->status === 'overdue' => 'late',
                $number === $currentNumber => 'current',
                default => 'upcoming',
            };

            $schedule[] = (object) [
                'number' => $number,
                'due_date' => $this->original_due_date->copy()->addMonthsNoOverflow($number - 1),
                // The upcoming installment shows amount_due (monthly_due plus
                // whatever shortfall was carried into it). The period that
                // shortfall was carried FROM — the one right before it —
                // shows what was actually paid for it instead of the flat
                // monthly_due, by working the same shortfall back out
                // (monthly_due - carried_over_amount). Only reconstructable
                // for that one most-recent period: carried_over_amount is a
                // single running value, not a per-period history, so an
                // older "paid" row has no way to recover what was actually
                // paid if it too was a partial payment.
                'amount' => match (true) {
                    $number === $currentNumber => $this->amount_due,
                    $number === $currentNumber - 1 && (float) $this->carried_over_amount > 0 => round($this->monthly_due - (float) $this->carried_over_amount, 2),
                    default => $this->monthly_due,
                },
                'status' => $status,
            ];
        }

        return $schedule;
    }

    /**
     * Schedule for a loan that has reached ₱0 balance. Every closing
     * payment type (payment/prepayment/partial, and payOffEarly()) leaves
     * current_installment_number frozen at the first period it never
     * individually advanced through — advanceDueDateForPayment() exits the
     * moment remaining_balance hits zero, before touching next_due_date.
     * That frozen number ($anchor) is exactly the period the closing
     * payment landed on:
     *   - periods before it were genuinely settled one at a time (same
     *     'paid'/monthly_due treatment as an active loan's past periods);
     *   - the anchor period shows the real closing payment's own ledger
     *     amount instead of a nominal monthly_due, since that one payment
     *     may have covered several periods' worth at once (a lump
     *     Payment/Prepayment, or payOffEarly()'s waived-interest payoff);
     *   - periods after the anchor were never paid individually at all,
     *     so instead of repeating "nothing was paid here on its own" once
     *     per leftover period, they collapse into a single closing row.
     */
    private function fullyPaidInstallmentSchedule(int $anchor): array
    {
        $schedule = [];

        for ($number = 1; $number < $anchor; $number++) {
            $schedule[] = (object) [
                'number' => $number,
                'due_date' => $this->original_due_date->copy()->addMonthsNoOverflow($number - 1),
                'amount' => $this->monthly_due,
                'status' => 'paid',
                'closing' => false,
            ];
        }

        $closingPayment = $this->payments
            ->whereIn('type', ['payment', 'prepayment', 'partial'])
            ->sortBy([['transaction_date', 'asc'], ['id', 'asc']])
            ->last();

        $schedule[] = (object) [
            'number' => $anchor,
            'due_date' => $this->original_due_date->copy()->addMonthsNoOverflow($anchor - 1),
            'amount' => $closingPayment ? (float) $closingPayment->amount : $this->monthly_due,
            'status' => 'paid',
            'closing' => true,
            'closing_type' => $closingPayment?->type,
        ];

        if ($anchor < $this->effective_term_months) {
            $schedule[] = (object) [
                'status' => 'collapsed',
                'from' => $anchor + 1,
                'to' => $this->effective_term_months,
            ];
        }

        return $schedule;
    }

    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /**
     * Opt-in filter for list/report queries only — see
     * LoanRequest::scopeVisibleAccount(). Must never be called from
     * applyOverdueInterest()/applyGracePeriodPolicy() or the scheduled
     * delinquency command: a deactivated login doesn't pause real debt.
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('loanRequest.farmer.account', fn ($q) => $q->where('status', 'inactive'));
    }

    /**
     * Release funds for a finalized loan, starting its repayment clock from
     * the disbursement date rather than whenever the manager finalized terms.
     */
    public function disburse(string $method, ?string $referenceNo, ?int $disbursedBy, ?string $disbursedAt = null): void
    {
        $disbursedAt = $disbursedAt ? now()->parse($disbursedAt) : now();
        $firstDueDate = $disbursedAt->copy()->addMonthNoOverflow()->toDateString();

        $this->update([
            'status' => 'active',
            // Principal + total flat interest for the full term, not just
            // principal — so "Current Balance" already IS the full amount
            // owed (matches Total Repayable) instead of two separate figures.
            'remaining_balance' => $this->monthly_due * $this->repayment_terms_months,
            'next_due_date' => $firstDueDate,
            // Fixed anchor for the delinquency clock — next_due_date advances
            // every time interest is applied, but this never does.
            'original_due_date' => $firstDueDate,
            'disbursed_at' => $disbursedAt,
            'disbursement_method' => $method,
            'reference_no' => $referenceNo,
            'disbursed_by' => $disbursedBy,
        ]);

        $this->notifyFarmer(
            'loan_disbursed',
            'Loan Disbursed',
            'Your loan of '.peso($this->principal_amount).' has been disbursed. Repayment begins '.now()->parse($firstDueDate)->format('M d, Y').'.',
        );
    }

    /**
     * The farmer this loan belongs to, reached through its originating request
     * rather than duplicating a farmer reference on the loan itself.
     */
    public function getFarmerAttribute(): ?Farmer
    {
        return $this->loanRequest?->farmer;
    }

    /**
     * Total farmer payments recorded against this loan — regular, prepaid,
     * and partial payments alike — excluding the system-applied
     * interest/penalty charges, which are their own ledger entries (type
     * 'interest').
     */
    public function getAmountPaidAttribute(): float
    {
        return round((float) $this->payments->whereIn('type', ['payment', 'prepayment', 'partial'])->sum('amount'), 2);
    }

    /**
     * The delinquency penalty charged so far. Distinguished from regular
     * interest by notes text since both share the 'interest' ledger type —
     * applyPenaltyCharge()'s grace-expiry charge is the only source of a
     * "penalty applied" note.
     */
    public function getPenaltyChargedAttribute(): float
    {
        return round((float) $this->payments->where('type', 'interest')
            ->filter(fn (LoanPayment $payment) => str_contains($payment->notes ?? '', 'penalty applied'))
            ->sum('amount'), 2);
    }

    /**
     * Every system-applied charge for falling behind — missed-due-date
     * interest and the grace/penalty charges alike, both stored as the same
     * 'interest' ledger type — summed and counted together for the "Late
     * charges added" line of the balance breakdown.
     */
    public function getLateChargesAttribute(): array
    {
        $charges = $this->payments->where('type', 'interest');

        return [
            'total' => round((float) $charges->sum('amount'), 2),
            'count' => $charges->count(),
        ];
    }

    /**
     * A human-readable version of this loan's full history, for display: a
     * synthetic "loan released" row (the disbursement isn't a ledger entry),
     * followed by one row per payment/interest entry, each narrated against
     * a replay of advanceDueDateForPayment() so it can say which due date a
     * payment was for, whether it landed on time, and what the due date
     * became afterward.
     *
     * The replay runs against a *clone*, never this loan's own loaded
     * attributes — private methods are callable across instances of the
     * same class in PHP, so the clone can still drive
     * advanceDueDateForPayment()/currentPeriodCost() unchanged without this
     * accessor corrupting the real $loan the rest of the page is rendering
     * from. remaining_balance is replayed too (same ledger-derived
     * starting-balance approach as recalculateSchedule()), purely so
     * advanceDueDateForPayment()'s own "nothing left to advance" guard
     * behaves correctly throughout — the displayed "balance" column always
     * comes straight from each entry's own stored balance_after, never from
     * the replay.
     */
    public function getNarratedHistoryAttribute(): array
    {
        $rows = [];

        if ($this->disbursed_at) {
            $totalToRepay = round($this->monthly_due * $this->repayment_terms_months, 2);
            $rate = rtrim(rtrim(number_format((float) $this->interest_rate, 2), '0'), '.');

            $rows[] = [
                'date' => $this->disbursed_at,
                'summary' => sprintf(
                    '%s given in %s. Total to repay set to %s (includes %d months of %s%% interest).',
                    peso($this->principal_amount),
                    str_replace('_', ' ', $this->disbursement_method ?? 'cash'),
                    peso($totalToRepay),
                    $this->repayment_terms_months,
                    $rate
                ),
                'paid' => null,
                'charged' => null,
                'balance' => $totalToRepay,
            ];
        }

        if (! $this->original_due_date) {
            return $rows;
        }

        $ledger = $this->payments()
            ->whereIn('type', ['payment', 'prepayment', 'partial', 'interest'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $clone = clone $this;
        $clone->next_due_date = $this->original_due_date;
        $clone->current_period_paid = 0;
        $clone->carried_over_amount = 0;

        if ($first = $ledger->first()) {
            $clone->remaining_balance = $first->type === 'interest'
                ? round((float) $first->balance_after - (float) $first->amount, 2)
                : round((float) $first->balance_after + (float) $first->amount, 2);
        }

        foreach ($ledger as $entry) {
            $dueBefore = $clone->next_due_date->copy();

            if ($entry->type === 'interest') {
                $clone->remaining_balance = round((float) $clone->remaining_balance + (float) $entry->amount, 2);
                $clone->next_due_date = $dueBefore->copy()->addMonthNoOverflow();

                $rows[] = [
                    'date' => $entry->transaction_date,
                    'summary' => $entry->notes ?: 'Interest applied for a missed due date.',
                    'paid' => null,
                    'charged' => (float) $entry->amount,
                    'balance' => (float) $entry->balance_after,
                ];

                continue;
            }

            if (str_contains($entry->notes ?? '', 'Paid off early')) {
                $clone->remaining_balance = 0;
                $clone->current_period_paid = 0;
                $clone->carried_over_amount = 0;

                $rows[] = [
                    'date' => $entry->transaction_date,
                    'summary' => $entry->notes,
                    'paid' => (float) $entry->amount,
                    'charged' => null,
                    'balance' => (float) $entry->balance_after,
                ];

                continue;
            }

            $clone->remaining_balance = max(0, round((float) $clone->remaining_balance - (float) $entry->amount, 2));
            $clone->advanceDueDateForPayment((float) $entry->amount, $entry->type);

            $dueAfter = $clone->next_due_date->copy();
            $monthsClosed = (int) round($dueBefore->diffInMonths($dueAfter));

            $summary = match (true) {
                $entry->type === 'partial' => sprintf(
                    'Partial payment for the %s due date — %s carried forward. Next due %s.',
                    $dueBefore->format('M j, Y'),
                    peso((float) $clone->carried_over_amount),
                    $dueAfter->format('M j, Y')
                ),
                $monthsClosed === 0 => sprintf(
                    'Payment toward the %s due date — not yet enough to close it.',
                    $dueBefore->format('M j, Y')
                ),
                $monthsClosed === 1 => $entry->transaction_date->lte($dueBefore)
                    ? sprintf('On time, for the %s due date. Next due %s.', $dueBefore->format('M j, Y'), $dueAfter->format('M j, Y'))
                    : sprintf('Counted for the %s due date. Next due %s.', $dueBefore->format('M j, Y'), $dueAfter->format('M j, Y')),
                default => sprintf(
                    'Covers %d installments starting %s. Next due %s.',
                    $monthsClosed,
                    $dueBefore->format('M j, Y'),
                    $dueAfter->format('M j, Y')
                ),
            };

            if ($entry->notes) {
                $summary .= ' '.$entry->notes;
            }

            $rows[] = [
                'date' => $entry->transaction_date,
                'summary' => $summary,
                'paid' => (float) $entry->amount,
                'charged' => null,
                'balance' => (float) $entry->balance_after,
            ];
        }

        return $rows;
    }

    /**
     * Total interest: the contractual interest already folded into
     * remaining_balance at disbursement (monthly_due * term - principal),
     * plus any extra overdue/grace interest charged since. Excludes the
     * delinquency penalty (see penalty_charged).
     */
    public function getInterestChargedAttribute(): float
    {
        $contractual = round(($this->monthly_due * $this->effective_term_months) - (float) $this->principal_amount - $this->waived_interest, 2);

        $extra = (float) $this->payments->where('type', 'interest')
            ->reject(fn (LoanPayment $payment) => str_contains($payment->notes ?? '', 'penalty applied'))
            ->sum('amount');

        return round($contractual + $extra, 2);
    }

    /**
     * How much future interest an early payoff waived, for reporting
     * (getInterestChargedAttribute()/getTotalAmountAttribute() would
     * otherwise overstate a paid-off-early loan's interest, since they
     * assume the full term's interest was always charged). Recomputed from
     * current_installment_number/effective_term_months rather than parsed
     * back out of the payment note's text — both stay stable after
     * payOffEarly() (next_due_date is deliberately never touched), so this
     * reproduces the exact figure interest_saved had at the moment of
     * payoff, reliably, without depending on a specific note wording.
     */
    public function getWaivedInterestAttribute(): float
    {
        if (! $this->paid_off_early || $this->current_installment_number === null) {
            return 0.0;
        }

        $futurePeriods = max(0, $this->effective_term_months - $this->current_installment_number);
        $interestPortion = round($this->monthly_due - (float) $this->installment_amount, 2);

        return round($interestPortion * $futurePeriods, 2);
    }

    public function getTotalAmountAttribute(): float
    {
        return round((float) $this->principal_amount + $this->interest_charged + $this->penalty_charged, 2);
    }

    /**
     * A single, report-friendly status folding both the loan's lifecycle
     * status and its payment progress into the four buckets managers think
     * in: Active / Overdue / Partial / Paid.
     */
    public function getDisplayStatusAttribute(): string
    {
        return match (true) {
            $this->status === 'fully_paid' => 'Paid',
            $this->status === 'overdue' => 'Overdue',
            $this->amount_paid > 0 => 'Partial',
            default => 'Active',
        };
    }

    /**
     * How much principal is still owed, for the Prepayment form's payoff
     * preview — anchored to principal_amount directly (not derived from
     * remaining_balance, which has interest folded in), matching how the
     * payoff amount itself is built up from this same figure.
     */
    public function getRemainingPrincipalAttribute(): ?float
    {
        if ($this->current_installment_number === null) {
            return null;
        }

        return max(0, round((float) $this->principal_amount - (float) $this->installment_amount * ($this->current_installment_number - 1), 2));
    }

    /**
     * How much it costs to pay this loan off completely today: the real
     * current balance, minus interest for every period that hasn't started
     * yet. Today's own interest (and any overdue interest/penalty already
     * charged — real, already-incurred debt, not a future period) stays;
     * only interest for periods strictly after the current one is waived.
     *
     * Deliberately anchored to remaining_balance rather than rebuilt from
     * principal_amount alone — remaining_balance already correctly folds in
     * any overdue interest/grace penalty, which must still be collected in
     * full. Rebuilding from principal would silently waive that too. This
     * does mean the figure can differ from a hand calculation by a few
     * centavos on some loans, for the same reason every other balance
     * figure in this app can: installment_amount is principal_amount
     * divided and rounded, so monthly_due × term doesn't land on exactly
     * principal_amount + nominal interest — remaining_balance already
     * carries that same small remainder from disbursement onward.
     */
    public function getPayoffAmountAttribute(): ?float
    {
        if (! in_array($this->status, ['active', 'overdue'], true) || $this->current_installment_number === null) {
            return null;
        }

        $futurePeriods = max(0, $this->effective_term_months - $this->current_installment_number);
        $interestPortion = round($this->monthly_due - (float) $this->installment_amount, 2);
        $futureInterest = round($interestPortion * $futurePeriods, 2);

        return max(0, round((float) $this->remaining_balance - $futureInterest, 2));
    }

    /**
     * How much future interest paying off today, instead of on schedule,
     * waives — for display ("pay now and save ₱X").
     */
    public function getInterestSavedAttribute(): float
    {
        if ($this->payoff_amount === null) {
            return 0.0;
        }

        return round((float) $this->remaining_balance - $this->payoff_amount, 2);
    }

    /**
     * Whether this loan was closed out via an early payoff (waiving future
     * interest) rather than by running its full course — read back from the
     * marker payOffEarly() leaves in its payment's notes, the same way
     * getPenaltyChargedAttribute() distinguishes penalty-interest entries
     * without a dedicated column.
     */
    public function getPaidOffEarlyAttribute(): bool
    {
        return $this->status === 'fully_paid'
            && $this->payments->contains(fn (LoanPayment $payment) => str_contains($payment->notes ?? '', 'Paid off early'));
    }

    /**
     * Record a farmer payment against this loan, reducing the balance and
     * closing the loan out once it reaches zero. A Prepayment for exactly
     * the current payoff_amount is treated as an early payoff instead (see
     * payOffEarly()) — everything else follows the normal period-by-period
     * advancement below.
     */
    public function recordPayment(float $amount, ?string $notes, ?int $recordedBy, string $type = 'payment'): LoanPayment
    {
        if ($type === 'prepayment' && $this->payoff_amount !== null && abs($amount - $this->payoff_amount) < 0.01) {
            return $this->payOffEarly($amount, $notes, $recordedBy);
        }

        $this->remaining_balance = max(0, round((float) $this->remaining_balance - $amount, 2));
        $this->status = $this->remaining_balance <= 0 ? 'fully_paid' : 'active';

        $this->advanceDueDateForPayment($amount, $type);

        $this->save();

        return $this->payments()->create([
            'type' => $type,
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'balance_after' => $this->remaining_balance,
            'notes' => $notes,
            'recorded_by' => $recordedBy,
        ]);
    }

    /**
     * Closes the loan out completely, waiving every remaining period's
     * interest. Jumps straight to "fully paid" rather than routing through
     * advanceDueDateForPayment()'s period-by-period loop, which can only
     * close a period by paying its full nominal cost — the whole point of
     * an early payoff is that future periods are NOT being paid in full.
     * next_due_date is deliberately left as-is, matching how a loan paid
     * off exactly on schedule already behaves (see advanceDueDateForPayment
     * ()'s own early-exit once remaining_balance hits zero) — nothing reads
     * it once the loan is closed; getInstallmentScheduleAttribute() uses
     * status, not next_due_date, to tell "paid off early" from "in
     * progress".
     */
    private function payOffEarly(float $amount, ?string $notes, ?int $recordedBy): LoanPayment
    {
        $waived = $this->interest_saved;

        $this->remaining_balance = 0;
        $this->current_period_paid = 0;
        $this->carried_over_amount = 0;
        $this->status = 'fully_paid';
        $this->save();

        $marker = $waived > 0 ? 'Paid off early — '.peso($waived).' in future interest waived.' : 'Paid off early.';

        return $this->payments()->create([
            'type' => 'prepayment',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'balance_after' => 0,
            'notes' => trim($marker.($notes ? ' '.$notes : '')),
            'recorded_by' => $recordedBy,
        ]);
    }

    /**
     * A full period's cost right now: the standard monthly_due, plus
     * whatever shortfall a prior "partial" payment left behind. This is the
     * amount a 'payment'/'prepayment' must clear to close the current
     * period and roll forward to the next one.
     */
    private function currentPeriodCost(): float
    {
        return round($this->monthly_due + (float) $this->carried_over_amount, 2);
    }

    /**
     * Applies a payment oldest-period-first: the payment (plus whatever was
     * already paid toward the current period) is pooled, then that pool
     * closes out as many full periods as it covers — one at a time, in due
     * order, advancing next_due_date and clearing carried_over_amount for
     * each one — before whatever's left over (less than a full period) is
     * parked as progress toward the next open period. A single large
     * prepayment can close several months at once this way, with the exact
     * remainder carrying forward automatically. Applies identically to
     * regular and batch loans, since both are plain Loan records going
     * through this same method.
     *
     * A "partial" payment is the one exception: rather than leaving the
     * period open until it's topped up, it closes the period out
     * immediately — however much of it got paid — and rolls whatever's
     * still short into carried_over_amount, which currentPeriodCost() folds
     * into the next period's due figure until it's paid off.
     *
     * Amounts are rounded to 2 decimals at every step (pesos/centavos) so
     * floating-point drift can never leave a fraction-of-a-centavo balance
     * behind or loop forever chasing it.
     */
    private function advanceDueDateForPayment(float $amount, string $type): void
    {
        if ($this->remaining_balance <= 0 || ! $this->next_due_date) {
            $this->current_period_paid = 0;
            $this->carried_over_amount = 0;

            return;
        }

        $this->current_period_paid = round((float) $this->current_period_paid + $amount, 2);

        if ($type === 'partial') {
            $periodCost = $this->currentPeriodCost();
            $this->carried_over_amount = max(0, round($periodCost - $this->current_period_paid, 2));
            $this->current_period_paid = 0;
            $this->next_due_date = $this->next_due_date->copy()->addMonthNoOverflow();

            return;
        }

        $pool = $this->current_period_paid;
        $this->current_period_paid = 0;

        // A centavo's worth of float-rounding tolerance, so "pool exactly
        // equals this period's cost" reliably closes the period instead of
        // sometimes landing a hair short/over and either stalling the loop
        // or leaving a phantom fraction of a centavo as "still owed".
        $epsilon = 0.005;

        while ($pool > $epsilon) {
            $periodCost = $this->currentPeriodCost();

            if ($pool + $epsilon >= $periodCost) {
                $pool = round($pool - $periodCost, 2);
                $this->carried_over_amount = 0;
                $this->next_due_date = $this->next_due_date->copy()->addMonthNoOverflow();
            } else {
                $this->current_period_paid = $pool;
                $pool = 0;
            }
        }
    }

    /**
     * Re-derive next_due_date/current_period_paid/carried_over_amount from
     * this loan's own payment ledger, replayed from disbursement forward
     * through the logic above — for loans whose schedule was corrupted by
     * advanceDueDateForPayment()'s pre-fix bug (a payment reaching or
     * exceeding one period's cost failed to advance the due date at all,
     * silently stranding the overage). Leaves pending_disbursement/archived
     * loans and penalty timestamps untouched.
     *
     * remaining_balance's own running subtraction was never part of this
     * bug, so it's deliberately NOT recomputed from today's disburse()
     * formula (monthly_due × term) — some real loans were disbursed under
     * an earlier balance convention, and assuming today's formula applied
     * retroactively would silently rewrite their actual terms. Instead the
     * true starting balance is read back out of the ledger's own first
     * entry, the replay is cross-checked against the loan's real current
     * balance, and only loans where it matches get saved — anything else is
     * reported back as a mismatch for a human to look at, rather than
     * risking an incorrect write to real debt figures.
     *
     * @return array{changed: bool, mismatch: bool}
     */
    public function recalculateSchedule(): array
    {
        if (! $this->original_due_date || ! in_array($this->status, ['active', 'overdue', 'fully_paid'], true)) {
            return ['changed' => false, 'mismatch' => false];
        }

        $ledger = $this->payments()
            ->whereIn('type', ['payment', 'prepayment', 'partial', 'interest'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $actualBalance = (float) $this->remaining_balance;
        $workingBalance = $actualBalance;

        if ($first = $ledger->first()) {
            $workingBalance = $first->type === 'interest'
                ? round((float) $first->balance_after - (float) $first->amount, 2)
                : round((float) $first->balance_after + (float) $first->amount, 2);
        }

        $this->next_due_date = $this->original_due_date;
        $this->current_period_paid = 0;
        $this->carried_over_amount = 0;
        $this->remaining_balance = $workingBalance;

        foreach ($ledger as $entry) {
            if ($entry->type === 'interest') {
                $this->remaining_balance = round((float) $this->remaining_balance + (float) $entry->amount, 2);
                $this->next_due_date = $this->next_due_date->copy()->addMonthNoOverflow();

                continue;
            }

            $this->remaining_balance = max(0, round((float) $this->remaining_balance - (float) $entry->amount, 2));
            $this->advanceDueDateForPayment((float) $entry->amount, $entry->type);
        }

        $mismatch = abs((float) $this->remaining_balance - $actualBalance) >= 0.01;

        // The replayed balance is only a cross-check, never the value that
        // gets saved — the loan's real, already-correct balance is what's
        // kept either way.
        $this->remaining_balance = $actualBalance;

        $this->status = match (true) {
            $this->remaining_balance <= 0 => 'fully_paid',
            $this->next_due_date->isPast() => 'overdue',
            default => 'active',
        };

        if (! $mismatch) {
            $this->save();
        }

        return ['changed' => ! $mismatch, 'mismatch' => $mismatch];
    }

    /**
     * Apply the 2% (or configured) interest rate for every due date that has
     * passed without payment, advancing the due date one month at a time
     * until it catches up to today. Returns how many charges were applied.
     */
    public function applyOverdueInterest(): int
    {
        $applied = 0;

        while (in_array($this->status, ['active', 'overdue']) && $this->next_due_date?->isPast()) {
            $interest = round((float) $this->remaining_balance * ((float) $this->interest_rate / 100), 2);
            $this->remaining_balance = round((float) $this->remaining_balance + $interest, 2);
            $this->status = 'overdue';
            $this->next_due_date = $this->next_due_date->copy()->addMonthNoOverflow();
            $this->save();

            $this->payments()->create([
                'type' => 'interest',
                'amount' => $interest,
                'transaction_date' => now()->toDateString(),
                'balance_after' => $this->remaining_balance,
                'notes' => "{$this->interest_rate}% interest applied for missed due date.",
            ]);

            $applied++;
        }

        return $applied;
    }

    /**
     * A loan is restricted (blocks the farmer from any new loan request) once
     * the grace-period penalty has fired and money is still owed. Clears
     * itself automatically once the balance reaches zero.
     */
    public function getIsRestrictedAttribute(): bool
    {
        return $this->grace_penalty_applied_at !== null && (float) $this->remaining_balance > 0;
    }

    /**
     * The current delinquency escalation stage, for display. Null means the
     * loan isn't delinquent (or hasn't been disbursed yet).
     */
    public function getDelinquencyStageAttribute(): ?string
    {
        if ((float) $this->remaining_balance <= 0) {
            return null;
        }

        return match (true) {
            $this->legal_action_at !== null => 'legal_action',
            $this->barangay_summon_at !== null => 'barangay_summon',
            $this->grace_penalty_applied_at !== null => 'penalized',
            $this->partial_penalty_applied_at !== null => 'grace',
            default => null,
        };
    }

    /**
     * Cooperative delinquency policy, anchored to original_due_date (which
     * never moves, unlike next_due_date):
     *   - Within 30 days past due, still unpaid: one-time +2% on the
     *     remaining balance ("grace period interest").
     *   - Past the 30-day grace period, still unpaid: one-time +10% penalty
     *     on the (now larger) outstanding balance, and the loan is
     *     restricted from new loan requests until fully paid.
     *   - 5 months past due, still unpaid: flagged for Barangay summons.
     *   - 6 months past due, still unpaid: flagged for legal action.
     * Every step is guarded by its own "already applied" timestamp, so this
     * is safe to call repeatedly (page loads, the daily scheduled job).
     */
    public function applyGracePeriodPolicy(): void
    {
        if (! in_array($this->status, ['active', 'overdue'], true)) {
            return;
        }

        if ($this->original_due_date === null || $this->original_due_date->isFuture()) {
            return;
        }

        if ((float) $this->remaining_balance <= 0) {
            return;
        }

        $graceExpiresAt = $this->original_due_date->copy()->addDays(self::GRACE_PERIOD_DAYS);
        $loanCode = 'LN-'.str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
        $farmerName = $this->farmer->full_name;

        if ($this->partial_penalty_applied_at === null && now()->greaterThanOrEqualTo($this->original_due_date)) {
            $this->applyPenaltyCharge(
                rate: self::PARTIAL_PENALTY_RATE,
                notes: sprintf('%s%% grace-period interest applied on the unpaid balance (due date passed, still within the %d-day grace period).', self::PARTIAL_PENALTY_RATE, self::GRACE_PERIOD_DAYS),
            );
            $this->partial_penalty_applied_at = now();
            $this->save();

            $this->notifyStaff(
                'loan_grace_interest',
                "Grace Period Interest — {$farmerName}",
                "{$loanCode}: ".self::PARTIAL_PENALTY_RATE.'% grace-period interest applied to the unpaid balance.',
            );

            $this->notifyFarmer(
                'loan_grace_interest',
                'Grace Period Interest Applied',
                "{$loanCode}: A ".self::PARTIAL_PENALTY_RATE.'% grace-period interest has been applied to your unpaid balance. Please settle your loan to avoid further penalties.',
            );
        }

        if ($this->grace_penalty_applied_at === null && now()->greaterThan($graceExpiresAt)) {
            $this->applyPenaltyCharge(
                rate: self::GRACE_PENALTY_RATE,
                notes: sprintf('%s%% penalty applied: grace period expired without the balance being settled.', self::GRACE_PENALTY_RATE),
            );
            $this->grace_penalty_applied_at = now();
            $this->save();

            $this->notifyStaff(
                'loan_penalty',
                "Loan Penalized — {$farmerName}",
                "{$loanCode}: ".self::GRACE_PENALTY_RATE.'% penalty applied. Restricted from new loans until paid in full.',
            );

            $this->notifyFarmer(
                'loan_penalty',
                'Loan Penalty Applied',
                "{$loanCode}: Your loan is now overdue past the grace period. A ".self::GRACE_PENALTY_RATE.'% penalty has been applied, and you are restricted from new loans until this balance is fully paid.',
            );
        }

        if ($this->barangay_summon_at === null && now()->greaterThanOrEqualTo($this->original_due_date->copy()->addMonths(self::BARANGAY_SUMMON_MONTHS))) {
            $this->barangay_summon_at = now();
            $this->save();

            $this->notifyStaff(
                'loan_barangay_summon',
                "Barangay Summons Flagged — {$farmerName}",
                "{$loanCode}: unpaid for ".self::BARANGAY_SUMMON_MONTHS.' months. Flagged for Barangay summons.',
            );

            $this->notifyFarmer(
                'loan_barangay_summon',
                'Barangay Summons Notice',
                "{$loanCode}: Your loan has been unpaid for ".self::BARANGAY_SUMMON_MONTHS.' months and has been flagged for Barangay summons. Please contact the cooperative immediately.',
            );
        }

        if ($this->legal_action_at === null && now()->greaterThanOrEqualTo($this->original_due_date->copy()->addMonths(self::LEGAL_ACTION_MONTHS))) {
            $this->legal_action_at = now();
            $this->save();

            $this->notifyStaff(
                'loan_legal_action',
                "Legal Action Flagged — {$farmerName}",
                "{$loanCode}: unpaid for ".self::LEGAL_ACTION_MONTHS.' months. Flagged for legal action per the notarized promissory note.',
            );

            $this->notifyFarmer(
                'loan_legal_action',
                'Legal Action Notice',
                "{$loanCode}: Your loan has been unpaid for ".self::LEGAL_ACTION_MONTHS.' months and has been flagged for legal action per your notarized promissory note. Please contact the cooperative immediately.',
            );
        }
    }

    /**
     * Add a percentage-of-balance penalty charge to the ledger and reflect it
     * in the outstanding balance, mirroring applyOverdueInterest()'s pattern.
     */
    private function applyPenaltyCharge(float $rate, string $notes): void
    {
        $charge = round((float) $this->remaining_balance * ($rate / 100), 2);
        $this->remaining_balance = round((float) $this->remaining_balance + $charge, 2);
        $this->status = 'overdue';
        $this->save();

        $this->payments()->create([
            'type' => 'interest',
            'amount' => $charge,
            'transaction_date' => now()->toDateString(),
            'balance_after' => $this->remaining_balance,
            'notes' => $notes,
        ]);
    }

    /**
     * Broadcast a system alert to every Admin and Manager account.
     */
    private function notifyStaff(string $type, string $title, string $message): void
    {
        Notification::notifyRoles([1, 2], $title, $message, $type, ['loan_id' => $this->id]);
    }

    /**
     * Notify the farmer's own login account, if they have one.
     */
    private function notifyFarmer(string $type, string $title, string $message): void
    {
        $accountId = $this->farmer?->account_user_id;

        if ($accountId) {
            Notification::notify($accountId, $title, $message, $type, ['loan_id' => $this->id]);
        }
    }
}
