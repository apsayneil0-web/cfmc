<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'loan_request_id',
        'principal_amount',
        'interest_rate',
        'repayment_terms_months',
        'installment_amount',
        'collateral',
        'remaining_balance',
        'next_due_date',
        'status',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
            'archived_at' => 'datetime',
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
    public function recordPayment(float $amount, ?string $notes, ?int $recordedBy): LoanPayment
    {
        $this->remaining_balance = max(0, round((float) $this->remaining_balance - $amount, 2));
        $this->status = $this->remaining_balance <= 0 ? 'fully_paid' : 'active';
        $this->save();

        return $this->payments()->create([
            'type' => 'payment',
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
}
