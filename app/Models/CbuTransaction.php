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
        'recorded_by',
    ];

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
