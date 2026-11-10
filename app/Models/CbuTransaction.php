<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbuTransaction extends Model
{
    protected $fillable = [
        'cbu_id',
        'type',
        'category',
        'amount',
        'transaction_date',
        'balance_after',
        'notes',
        'proof_document',
        'recorded_by',
    ];

    /**
     * Public URL of the attached proof of payment, or null when none is on
     * file (older entries, expenses).
     */
    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_document ? asset('storage/'.$this->proof_document) : null;
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function cbu(): BelongsTo
    {
        return $this->belongsTo(Cbu::class);
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
        return $query->whereDoesntHave('cbu.farmer.account', fn ($q) => $q->where('status', 'inactive'));
    }
}
