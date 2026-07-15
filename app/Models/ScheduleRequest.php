<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleRequest extends Model
{
    /**
     * Static machinery list with availability status.
     * No machinery inventory backend exists yet, so availability is a fixed reference list.
     */
    public const MACHINERY = [
        ['name' => 'Harvester', 'status' => 'Available'],
        ['name' => 'Tractor', 'status' => 'Available'],
        ['name' => 'Pump Boat', 'status' => 'Unavailable'],
    ];

    /**
     * Minimum number of days of advance notice required before a schedule's
     * date, for both farmer requests and manager-created/assigned schedules.
     */
    public const MIN_LEAD_DAYS = 3;

    /**
     * The earliest date a new or rescheduled/reassigned booking may fall on,
     * given the minimum lead-time requirement.
     */
    public static function earliestAllowedDate(): Carbon
    {
        return now()->addDays(self::MIN_LEAD_DAYS)->startOfDay();
    }

    protected $fillable = [
        'user_id',
        'farmer_name',
        'member_type',
        'machinery',
        'land_size',
        'scheduled_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'is_reschedule',
        'original_schedule_id',
        'denial_reason',
        'remarks',
        'harvest_yield',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'is_reschedule' => 'boolean',
            'harvest_yield' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originalSchedule(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_schedule_id');
    }

    public function rescheduleRequests(): HasMany
    {
        return $this->hasMany(self::class, 'original_schedule_id');
    }

    /**
     * Display name for the requester: linked farmer's full name, account name,
     * or the manually entered name for non-member/manager-created entries.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->farmer?->full_name ?? $this->user->name;
        }

        return $this->farmer_name ?? 'Unknown';
    }

    /**
     * Whether this request overlaps with another active (pending/approved) request
     * for the same machinery and date. Used to prevent double-booking.
     */
    public static function hasConflict(string $machinery, string $date, ?string $startTime, ?string $endTime, ?int $excludeId = null): bool
    {
        $query = self::where('machinery', $machinery)
            ->where('scheduled_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('archived_at');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($startTime && $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });
        }

        return $query->exists();
    }

    /**
     * Build a day-number => bookings map for the given month, showing only
     * approved schedules (pending/denied requests don't occupy the calendar).
     *
     * @return array<int, \Illuminate\Support\Collection>
     */
    public static function calendarForMonth(Carbon $month, ?string $machinery = null): array
    {
        $query = self::with('user.farmer')
            ->whereNull('archived_at')
            ->where('status', 'approved')
            ->whereBetween('scheduled_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);

        if ($machinery) {
            $query->where('machinery', $machinery);
        }

        $schedules = $query->get();

        $days = [];
        $cursor = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        while ($cursor->lte($end)) {
            $days[$cursor->day] = $schedules->filter(
                fn ($s) => $s->scheduled_date->isSameDay($cursor)
            )->values();
            $cursor->addDay();
        }

        return $days;
    }
}
