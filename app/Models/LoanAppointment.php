<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanAppointment extends Model
{
    /**
     * The cooperative's 5 fixed daily appointment slots, one hour each.
     * Having exactly 5 slots is what caps a day at 5 farmers — the limit
     * and the one-hour-each requirement are the same mechanism, not two
     * separate rules to keep in sync.
     */
    public const SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00'];

    protected $fillable = [
        'user_id',
        'appointment_date',
        'appointment_time',
        'purpose',
        'requested_amount',
        'loan_purpose',
        'repayment_terms_months',
        'collateral',
        'documents_path',
        'loan_request_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The official LoanRequest a Manager encoded from this appointment's
     * loan pre-request, once submitted — null until then.
     */
    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class);
    }

    /**
     * Slots (as "H:i" strings) still open on a given date — i.e. not held
     * by another non-cancelled appointment. $excludeId lets a reschedule
     * exclude the appointment's own current slot from the "taken" list.
     */
    public static function availableSlotsFor(string $date, ?int $excludeId = null): array
    {
        $taken = self::whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->map(fn (self $appt) => \Carbon\Carbon::parse($appt->appointment_time)->format('H:i'))
            ->all();

        return array_values(array_diff(self::SLOTS, $taken));
    }

    /**
     * Human-readable label for a slot, e.g. "8:00 AM – 9:00 AM".
     */
    public static function slotLabel(string $slot): string
    {
        $start = \Carbon\Carbon::createFromFormat('H:i', $slot);

        return $start->format('g:i A').' – '.$start->copy()->addHour()->format('g:i A');
    }
}
