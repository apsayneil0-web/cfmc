<?php

namespace App\Models;

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
}
