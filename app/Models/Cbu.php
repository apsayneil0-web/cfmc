<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cbu extends Model
{
    protected $fillable = [
        'farmer_id',
        'balance',
        'status',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CbuTransaction::class);
    }

    /**
     * Record a contribution or expense against this farmer's CBU balance,
     * mirroring Loan::recordPayment()'s ledger pattern.
     */
    public function recordTransaction(string $type, ?string $category, float $amount, ?string $notes, ?int $recordedBy): CbuTransaction
    {
        $this->balance = $type === 'expense'
            ? max(0, round((float) $this->balance - $amount, 2))
            : round((float) $this->balance + $amount, 2);
        $this->save();

        return $this->transactions()->create([
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'balance_after' => $this->balance,
            'notes' => $notes,
            'recorded_by' => $recordedBy,
        ]);
    }

    /**
     * Replay every transaction in chronological order to recompute each
     * one's balance_after and this account's current balance. Needed after
     * editing a transaction, since changing its amount/type shifts the
     * running balance for everything recorded after it.
     */
    public function recalculateBalances(): void
    {
        $balance = 0.0;

        $transactions = $this->transactions()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
            $balance = $transaction->type === 'expense'
                ? max(0, round($balance - (float) $transaction->amount, 2))
                : round($balance + (float) $transaction->amount, 2);

            $transaction->update(['balance_after' => $balance]);
        }

        $this->update(['balance' => $balance]);
    }
}
