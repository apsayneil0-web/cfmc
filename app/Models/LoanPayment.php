<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'type',
        'amount',
        'transaction_date',
        'balance_after',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Opt-in filter for list/report queries only — see
     * LoanRequest::scopeVisibleAccount().
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('loan.loanRequest.farmer.account', fn ($q) => $q->where('status', 'inactive'));
    }
}
