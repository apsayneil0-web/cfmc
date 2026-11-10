<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A recorded cooperative expense: what kind it was and how much. Expenses
 * are a plain record — there is no pending/paid step or payment flow.
 */
class Expense extends Model
{
    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'proof_document',
        'recorded_by',
    ];

    /**
     * Public URL of the attached proof (receipt / invoice), or null when
     * none is on file.
     */
    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_document ? asset('storage/'.$this->proof_document) : null;
    }

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
