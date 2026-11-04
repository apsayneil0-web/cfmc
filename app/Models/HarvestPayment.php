<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestPayment extends Model
{
    /**
     * Cooperative's cut of a farmer's reported harvest income — applies only
     * when machine_type is Harvester (see isPercentageBased()).
     */
    public const MEMBER_RATE = 9.00;

    public const NON_MEMBER_RATE = 12.00;

    /**
     * Flat payment rates (₱) for machine types that aren't harvest-value
     * dependent — a Tractor/Seeder's job doesn't scale with yield the way a
     * Harvester's does. Any type not listed here (including a custom one a
     * manager adds later) falls back to FIXED_RATE_DEFAULT.
     */
    public const FIXED_RATES = [
        'Tractor' => 2300.00,
        'Rice Seeder' => 3000.00,
    ];

    public const FIXED_RATE_DEFAULT = 2000.00;

    protected $fillable = [
        'farmer_id',
        'farmer_name',
        'member_type',
        'machine_type',
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
     * Opt-in filter for list/report queries only — see
     * LoanRequest::scopeVisibleAccount(). Non-member payments (farmer_id
     * null) have nothing to match and are always kept.
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('farmer.account', fn ($q) => $q->where('status', 'inactive'));
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

    /**
     * Only a Harvester's payment scales with reported harvest value — every
     * other machine type is a flat rate regardless of member/non-member.
     * Matched case-insensitively: this fleet has had inconsistent casing on
     * machine type/name before (e.g. "Tractor" vs "tractor").
     */
    public static function isPercentageBased(string $machineType): bool
    {
        return mb_strtolower(trim($machineType)) === 'harvester';
    }

    /**
     * Flat payment amount (₱) for a non-Harvester machine type.
     */
    public static function fixedRateFor(string $machineType): float
    {
        $normalized = mb_strtolower(trim($machineType));

        foreach (self::FIXED_RATES as $type => $rate) {
            if (mb_strtolower($type) === $normalized) {
                return $rate;
            }
        }

        return self::FIXED_RATE_DEFAULT;
    }
}
