<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'status',
        'recorded_by',
        'paid_at',
        'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Transition a pending expense to paid, recording who settled it and when.
     */
    public function markPaid(int $userId): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
            'paid_by' => $userId,
        ]);
    }
}
