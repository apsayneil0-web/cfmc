<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'status',
        'manager_response',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Opt-in filter for list/report queries only — see
     * LoanRequest::scopeVisibleAccount().
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('user', fn ($q) => $q->where('status', 'inactive'));
    }
}
