<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleRequest extends Model
{
    /**
     * Minimum number of days of advance notice required before a schedule's
     * date, for both farmer requests and manager-created/assigned schedules.
     */
    public const MIN_LEAD_DAYS = 3;

    /**
     * Estimated hours of machine time needed per hectare, used to auto-suggest
     * an End Time from Land Size and Start Time on the schedule forms.
     */
    public const HOURS_PER_HECTARE = 1.5;

    /**
     * The earliest date a new or rescheduled/reassigned booking may fall on,
     * given the minimum lead-time requirement.
     */
    public static function earliestAllowedDate(): Carbon
    {
        return now()->addDays(self::MIN_LEAD_DAYS)->startOfDay();
    }

    /**
     * Hectares already reserved for a machine on a given date, counting only
     * approved/completed bookings and excluding archived records. A pending
     * request has not been approved yet, so it doesn't hold capacity against
     * other farmers — the Manager resolves any conflict between competing
     * pending requests at approval time (see wouldExceedDailyCapacity() and
     * hasConflict() calls in ScheduleApprovalController::approve()).
     */
    public static function reservedHectares(int $machineId, string $date, ?int $excludeId = null): float
    {
        $query = self::where('machine_id', $machineId)
            ->where('scheduled_date', $date)
            ->whereIn('status', ['approved', 'completed'])
            ->whereNull('archived_at');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return (float) $query->sum('land_size');
    }

    /**
     * Hectares still available for a machine on a given date before its
     * own daily capacity (Machine::daily_hectare_limit) is reached.
     */
    public static function remainingCapacity(int $machineId, string $date, float $limit, ?int $excludeId = null): float
    {
        return max(0, $limit - self::reservedHectares($machineId, $date, $excludeId));
    }

    /**
     * Whether adding a booking of the given size would push the machine's
     * total reserved hectares for that date past its own daily capacity.
     */
    public static function wouldExceedDailyCapacity(int $machineId, string $date, float $landSize, float $limit, ?int $excludeId = null): bool
    {
        return self::reservedHectares($machineId, $date, $excludeId) + $landSize > $limit;
    }

    protected $fillable = [
        'user_id',
        'batch_id',
        'farmer_name',
        'contact_number',
        'member_type',
        'machinery',
        'machine_id',
        'land_size',
        'units_requested',
        'crop_id',
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
            'units_requested' => 'integer',
            'is_reschedule' => 'boolean',
            'harvest_yield' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
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
     * Other requests submitted in the same multi-machine batch (e.g. a
     * Tractor + Seeder requested together for one job), excluding this row.
     */
    public function batchSiblings(): HasMany
    {
        return $this->hasMany(self::class, 'batch_id', 'batch_id')->where('id', '!=', $this->id);
    }

    /**
     * Opt-in filter for list/report queries only — see
     * LoanRequest::scopeVisibleAccount(). Must never be called from
     * reservedHectares()/hasConflict()/calendarForMonth(): a deactivated
     * farmer's existing booking still occupies real machine capacity.
     * Walk-in entries (user_id null, farmer_name instead) are always kept.
     */
    public function scopeVisibleAccount(Builder $query): Builder
    {
        return $query->whereDoesntHave('user', fn ($q) => $q->where('status', 'inactive'));
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
     * Whether booking $unitsRequested more units of this machine for this
     * date/time would exceed how many physical units it has (Machine::quantity).
     * A machine record with quantity > 1 (e.g. 3 seeders) can service that many
     * overlapping bookings at once before it's actually a conflict.
     */
    public static function hasConflict(?int $machineId, string $date, ?string $startTime, ?string $endTime, ?int $excludeId = null, int $unitsRequested = 1): bool
    {
        if (! $machineId) {
            return false;
        }

        return $unitsRequested > self::unitsFree($machineId, $date, $startTime, $endTime, $excludeId);
    }

    /**
     * Physical units of a machine not yet taken by an approved booking
     * overlapping the given time window (the whole day when no times are
     * given). A pending request hasn't been approved yet, so it doesn't hold
     * a unit against other farmers — see reservedHectares() for why. Can go
     * negative if a machine was over-booked before its quantity was lowered;
     * callers that display it should clamp at zero.
     */
    public static function unitsFree(int $machineId, string $date, ?string $startTime = null, ?string $endTime = null, ?int $excludeId = null): int
    {
        $quantity = max(1, (int) (Machine::find($machineId)?->quantity ?? 1));

        $query = self::where('machine_id', $machineId)
            ->where('scheduled_date', $date)
            ->where('status', 'approved')
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

        return $quantity - (int) $query->sum('units_requested');
    }

    /**
     * Day-number => bool map for the given month: whether at least one
     * non-maintenance machine still has both hectare and unit capacity left
     * on that date. A day can have bookings and still be "available" here —
     * e.g. one machine fully booked but another (or the same one, if it has
     * spare quantity/hectares) still has room — which is what lets the
     * calendar stay clickable past the old "zero bookings" rule. Only
     * approved/completed bookings hold capacity — see reservedHectares()
     * and unitsFree() for why a pending request doesn't.
     *
     * @return array<int, bool>
     */
    public static function availabilityByDay(Carbon $month): array
    {
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $machines = Machine::whereNull('archived_at')->get();
        $outOfServiceIds = $machines->filter(fn (Machine $m) => $m->isOutOfService())->pluck('id')->all();

        $bookings = self::whereIn('status', ['approved', 'completed'])
            ->whereNull('archived_at')
            ->whereBetween('scheduled_date', [$monthStart, $monthEnd])
            ->get(['machine_id', 'scheduled_date', 'land_size', 'units_requested', 'status']);

        $reserved = [];
        foreach ($bookings as $booking) {
            $key = $booking->machine_id.'|'.$booking->scheduled_date->toDateString();
            $reserved[$key] ??= ['hectares' => 0.0, 'units' => 0];
            $reserved[$key]['hectares'] += (float) $booking->land_size;

            // unitsFree() only counts approved — a completed booking no
            // longer ties up a physical unit.
            if ($booking->status === 'approved') {
                $reserved[$key]['units'] += (int) $booking->units_requested;
            }
        }

        $available = [];
        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $dateStr = $cursor->toDateString();
            $hasCapacity = false;

            foreach ($machines as $machine) {
                if (in_array($machine->id, $outOfServiceIds, true)) {
                    continue;
                }

                $key = $machine->id.'|'.$dateStr;
                $reservedHectares = $reserved[$key]['hectares'] ?? 0.0;
                $reservedUnits = $reserved[$key]['units'] ?? 0;
                $quantity = max(1, (int) $machine->quantity);

                if ($reservedHectares < $machine->effective_daily_limit && $reservedUnits < $quantity) {
                    $hasCapacity = true;
                    break;
                }
            }

            $available[$cursor->day] = $hasCapacity;
            $cursor->addDay();
        }

        return $available;
    }

    /**
     * Build a day-number => bookings map for the given month, showing only
     * approved schedules (pending/denied requests don't occupy the calendar).
     *
     * @return array<int, \Illuminate\Support\Collection>
     */
    public static function calendarForMonth(Carbon $month, ?string $machinery = null, ?int $userId = null): array
    {
        // A pure display calendar (not used for capacity/conflict checks —
        // those go through reservedHectares()/hasConflict() instead), so
        // it's safe to hide a deactivated farmer's bookings here.
        $query = self::with('user.farmer')
            ->whereNull('archived_at')
            ->where('status', 'approved')
            ->visibleAccount()
            ->whereBetween('scheduled_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);

        if ($machinery) {
            $query->where('machinery', $machinery);
        }

        if ($userId) {
            $query->where('user_id', $userId);
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
