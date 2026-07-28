<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    /**
     * Usage hours at or beyond this threshold automatically flag a machine
     * as needing maintenance, mirroring the cooperative's own service policy.
     */
    public const MAINTENANCE_HOURS_THRESHOLD = 500;

    /**
     * Fallback daily hectare capacity for machines that predate the
     * per-machine limit column, or if it's ever left blank.
     */
    public const DEFAULT_DAILY_HECTARE_LIMIT = 6.00;

    protected $fillable = [
        'name',
        'brand',
        'serial_number',
        'quantity',
        'daily_hectare_limit',
        'assigned_operator',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'daily_hectare_limit' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    public function scheduleRequests(): HasMany
    {
        return $this->hasMany(ScheduleRequest::class);
    }

    /**
     * Total hours actually worked, summed from every completed booking's
     * start/end time. Replaces manual entry so the figure can't drift from
     * what was really scheduled.
     */
    public function getUsageHoursAttribute(): float
    {
        return round($this->scheduleRequests()
            ->where('status', 'completed')
            ->whereNull('archived_at')
            ->get()
            ->sum(fn (ScheduleRequest $r) => Carbon::parse($r->start_time)->diffInMinutes(Carbon::parse($r->end_time)) / 60), 1);
    }

    /**
     * How many bookings for this machine have been carried out.
     */
    public function getTimesUsedAttribute(): int
    {
        return $this->scheduleRequests()
            ->where('status', 'completed')
            ->whereNull('archived_at')
            ->count();
    }

    /**
     * Maintenance tier keyed off completed-use count, per the cooperative's
     * service policy: 1-25 routine, 26-50 basic, 51-100 full, 100+ comprehensive.
     */
    public function getMaintenanceLevelAttribute(): string
    {
        $uses = $this->times_used;

        return match (true) {
            $uses === 0 => 'none',
            $uses <= 25 => 'routine',
            $uses <= 50 => 'basic',
            $uses <= 100 => 'full',
            default => 'comprehensive',
        };
    }

    /**
     * Short badge label for the maintenance tier.
     */
    public function getMaintenanceLabelAttribute(): string
    {
        return match ($this->maintenance_level) {
            'none' => 'Not Yet Used',
            'routine' => 'Routine Inspection',
            'basic' => 'Basic Maintenance',
            'full' => 'Full Maintenance',
            'comprehensive' => 'Comprehensive Servicing',
        };
    }

    /**
     * Full maintenance recommendation text for the tier, per the cooperative's
     * usage-based service policy.
     */
    public function getMaintenanceRecommendationAttribute(): string
    {
        return match ($this->maintenance_level) {
            'none' => 'No servicing needed yet — this machine has not completed any bookings.',
            'routine' => 'Routine inspection only.',
            'basic' => 'Basic preventive maintenance (cleaning, lubrication, inspection).',
            'full' => 'Full preventive maintenance (oil, filters, belts, hydraulic checks).',
            'comprehensive' => 'Comprehensive inspection and servicing.',
        };
    }

    /**
     * Available, in use, or due for maintenance — derived from real bookings
     * and usage hours rather than a manually toggled flag.
     */
    public function getStatusAttribute(): string
    {
        $now = now();

        $currentlyBooked = $this->scheduleRequests()
            ->where('status', 'approved')
            ->whereNull('archived_at')
            ->whereDate('scheduled_date', $now->toDateString())
            ->whereTime('start_time', '<=', $now->format('H:i:s'))
            ->whereTime('end_time', '>=', $now->format('H:i:s'))
            ->exists();

        if ($currentlyBooked) {
            return 'in_use';
        }

        if ($this->usage_hours >= self::MAINTENANCE_HOURS_THRESHOLD) {
            return 'maintenance';
        }

        return 'available';
    }
}
