<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    /**
     * Append-only audit trail — no updated_at, entries are never edited.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Who performed the action. Null for the rare event with no
     * authenticated user (e.g. a failed login for an unknown username).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * What the action was about — a Farmer, a LoanRequest, a User, etc.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
