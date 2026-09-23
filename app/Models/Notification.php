<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'announcement_id',
        'loan_id',
        'schedule_id',
        'title',
        'message',
        'type',
        'is_read',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleRequest::class, 'schedule_id');
    }

    /**
     * Icon + short eyebrow label shown in the notification bell, keyed by type.
     */
    public static function typeDisplay(string $type): array
    {
        return match ($type) {
            'meeting' => ['icon' => '📅', 'label' => 'New Meeting Announcement'],
            'reminder' => ['icon' => '⏰', 'label' => 'Reminder'],
            'resolution' => ['icon' => '✅', 'label' => 'Resolution Posted'],
            'loan_grace_interest' => ['icon' => '⚠️', 'label' => 'Grace Period Interest Applied'],
            'loan_penalty' => ['icon' => '🚫', 'label' => 'Loan Penalty & Restriction'],
            'loan_barangay_summon' => ['icon' => '🏛️', 'label' => 'Barangay Summons Flagged'],
            'loan_legal_action' => ['icon' => '⚖️', 'label' => 'Legal Action Flagged'],
            'membership_approved' => ['icon' => '✅', 'label' => 'Membership Approved'],
            'membership_rejected' => ['icon' => '❌', 'label' => 'Membership Rejected'],
            'membership_application' => ['icon' => '📝', 'label' => 'New Membership Application'],
            'loan_approved' => ['icon' => '✅', 'label' => 'Loan Request Approved'],
            'loan_denied' => ['icon' => '❌', 'label' => 'Loan Request Denied'],
            'loan_finalized' => ['icon' => '📋', 'label' => 'Loan Terms Finalized'],
            'loan_disbursed' => ['icon' => '💰', 'label' => 'Loan Disbursed'],
            'schedule_approved' => ['icon' => '✅', 'label' => 'Schedule Approved'],
            'schedule_denied' => ['icon' => '❌', 'label' => 'Schedule Denied'],
            'schedule_completed' => ['icon' => '🏁', 'label' => 'Schedule Completed'],
            'complaint_response' => ['icon' => '💬', 'label' => 'Complaint Update'],
            'complaint_submitted' => ['icon' => '📩', 'label' => 'New Complaint Submitted'],
            'complaint_reopened' => ['icon' => '🔄', 'label' => 'Complaint Reopened'],
            'payment_recorded' => ['icon' => '🧾', 'label' => 'Payment Recorded'],
            'loan_appointment_booked' => ['icon' => '📆', 'label' => 'New Loan Appointment'],
            'loan_appointment_cancelled' => ['icon' => '🚫', 'label' => 'Loan Appointment Cancelled'],
            default => ['icon' => '📢', 'label' => 'New Announcement'],
        };
    }

    /**
     * Where this notification's "View" button should send the viewer, and
     * its label. Driven by type, and — for types shared across roles, like
     * loan delinquency alerts and membership decisions, which go out to
     * either a farmer or a staff account — by the recipient's role too,
     * since each role has its own equivalent page. Null hides the button.
     */
    public function getActionAttribute(): ?array
    {
        $role = (int) ($this->user->roleID ?? 0);

        return match ($this->type) {
            'reminder' => $this->schedule_id
                ? ['url' => route('farmer.schedule'), 'label' => 'View My Schedule']
                : ($role === 3 ? ['url' => route('farmer.loan-appointment'), 'label' => 'View Appointment'] : null),
            'membership_approved', 'membership_rejected' => $role === 3
                ? ['url' => route('farmer.dashboard'), 'label' => 'Go to Dashboard']
                : ['url' => route('manager.membership'), 'label' => 'View Membership'],
            'membership_application' => ['url' => route('admin.membership-approval'), 'label' => 'Review Application'],
            'loan_approved', 'loan_denied' => ['url' => route('farmer.loans'), 'label' => 'View My Loans'],
            'loan_finalized', 'loan_disbursed', 'loan_grace_interest', 'loan_penalty', 'loan_barangay_summon', 'loan_legal_action' => $role === 3
                ? ['url' => route('farmer.loans'), 'label' => 'View My Loan']
                : ['url' => route('manager.loan-management'), 'label' => 'View Loan Management'],
            'schedule_approved', 'schedule_denied', 'schedule_completed' => ['url' => route('farmer.schedule'), 'label' => 'View My Schedule'],
            'complaint_response' => ['url' => route('farmer.complaints'), 'label' => 'View Complaint'],
            'complaint_submitted', 'complaint_reopened' => ['url' => route('manager.complaints'), 'label' => 'View Complaints'],
            'loan_appointment_booked', 'loan_appointment_cancelled' => ['url' => route('manager.loan-appointment'), 'label' => 'View Appointments'],
            'payment_recorded' => $this->loan_id
                ? ['url' => route('farmer.loans'), 'label' => 'View My Loans']
                : ['url' => route('farmer.cbu'), 'label' => 'View CBU'],
            default => null,
        };
    }

    /**
     * Create one notification for a single user. Thin wrapper around
     * create() that fills in the boilerplate every call site otherwise
     * repeats (is_read/created_at), and accepts the same optional foreign
     * keys (loan_id, schedule_id, announcement_id) via $extra.
     */
    public static function notify(int $userId, string $title, string $message, string $type, array $extra = []): self
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
            ...$extra,
        ]);
    }

    /**
     * Broadcast a notification to every user holding one of the given roles
     * (1 = Admin, 2 = Manager, 3 = Farmer).
     */
    public static function notifyRoles(array $roleIds, string $title, string $message, string $type, array $extra = []): void
    {
        $rows = User::whereIn('roleID', $roleIds)->pluck('id')->map(fn ($userId) => [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => now(),
            ...$extra,
        ])->all();

        if (! empty($rows)) {
            static::insert($rows);
        }
    }
}
