<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanRequest extends Model
{
    protected $fillable = [
        'farmer_id',
        'requested_by',
        'type',
        'batch_id',
        'requested_amount',
        'purpose',
        'repayment_terms_months',
        'collateral',
        'documents_path',
        'status',
        'denial_reason',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LoanBatch::class, 'batch_id');
    }

    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }

    /**
     * Opt-in filter (not a global scope — see Loan/Cbu/LoanAppointment,
     * whose own internal recalculation/availability logic must never go
     * through this) for list/report queries: hides a request belonging to
     * a farmer whose login account is currently deactivated, so a Manager
     * deactivating an account hides that farmer everywhere a human looks,
     * without touching anything that recomputes real balances/obligations.
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('farmer.account', fn ($q) => $q->where('status', 'inactive'));
    }
}
