<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'capacity',
    ];

    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class, 'batch_id');
    }

    public function getLabelAttribute(): string
    {
        return "Batch {$this->batch_number}";
    }

    /**
     * Members currently occupying a slot in this batch. Denied or archived
     * requests never became (or stopped being) an active batch loan, so they
     * free up the slot for another farmer. Archiving a loan request only ever
     * sets archived_at (status stays whatever it was), so both must be checked.
     */
    public function getMemberCountAttribute(): int
    {
        return $this->loanRequests()
            ->whereNull('archived_at')
            ->where('status', '!=', 'denied')
            ->count();
    }

    public function getIsFullAttribute(): bool
    {
        return $this->member_count >= $this->capacity;
    }
}
