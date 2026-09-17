<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestPayment extends Model
{
    /**
     * Cooperative's cut of a farmer's reported harvest income.
     */
    public const MEMBER_RATE = 9.00;

    public const NON_MEMBER_RATE = 12.00;

    protected $fillable = [
        'farmer_id',
        'farmer_name',
        'member_type',
        'harvest_amount',
        'rate',
        'payment_amount',
        'payment_date',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Display name: the linked farmer's name for members, the manually
     * entered name for non-members.
     */
    public function getPayerNameAttribute(): string
    {
        return $this->farmer?->full_name ?? $this->farmer_name ?? 'Unknown';
    }

    /**
     * The applicable rate for a member/non-member, as a source of truth the
     * controller can call instead of trusting a client-submitted rate.
     */
    public static function rateFor(string $memberType): float
    {
        return $memberType === 'member' ? self::MEMBER_RATE : self::NON_MEMBER_RATE;
    }
}
