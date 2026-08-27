<?php

namespace App\Models;

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
            'remaining_balance' => $this->principal_amount,
            'next_due_date' => $firstDueDate,
            // Fixed anchor for the delinquency clock — next_due_date advances
            // every time interest is applied, but this never does.
            'original_due_date' => $firstDueDate,
            'disbursed_at' => $disbursedAt,
            'disbursement_method' => $method,
            'reference_no' => $referenceNo,
            'disbursed_by' => $disbursedBy,
        ]);
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
     * Record a farmer payment against this loan, reducing the balance and
     * closing the loan out once it reaches zero.
     */
    public function recordPayment(float $amount, ?string $notes, ?int $recordedBy, string $type = 'payment'): LoanPayment
    {
        $this->remaining_balance = max(0, round((float) $this->remaining_balance - $amount, 2));
        $this->status = $this->remaining_balance <= 0 ? 'fully_paid' : 'active';
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
        }

        if ($this->barangay_summon_at === null && now()->greaterThanOrEqualTo($this->original_due_date->copy()->addMonths(self::BARANGAY_SUMMON_MONTHS))) {
            $this->barangay_summon_at = now();
            $this->save();

            $this->notifyStaff(
                'loan_barangay_summon',
                "Barangay Summons Flagged — {$farmerName}",
                "{$loanCode}: unpaid for ".self::BARANGAY_SUMMON_MONTHS.' months. Flagged for Barangay summons.',
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
        $recipientIds = User::whereIn('roleID', [1, 2])->pluck('id');

        $rows = $recipientIds->map(fn ($userId) => [
            'user_id' => $userId,
            'loan_id' => $this->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
        ])->all();

        if (! empty($rows)) {
            Notification::insert($rows);
        }
    }
}
