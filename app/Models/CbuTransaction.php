<?php

namespace App\Models;

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
}
