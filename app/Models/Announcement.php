<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'purpose',
        'resolution',
        'announcement_date',
        'time',
        'location',
        'audience',
        'status',
        'created_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'announcement_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Farmer::class, 'announcement_recipients');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Human-readable summary of who this announcement is targeted at.
     */
    public function getAudienceLabelAttribute(): string
    {
        if ($this->audience === 'all_members') {
            return 'All Members';
        }

        return 'Selected ('.$this->recipients()->count().')';
    }

    /**
     * The notifications.type value this announcement's purpose maps to.
     */
    public function getNotificationTypeAttribute(): string
    {
        return match ($this->purpose) {
            'Meeting' => 'meeting',
            'Reminder' => 'reminder',
            'Resolution' => 'resolution',
            default => 'announcement',
        };
    }

    /**
     * Short (80-120 char) preview used as the notification's message field.
     */
    public function getNotificationMessageAttribute(): string
    {
        if ($this->purpose === 'Meeting') {
            $when = $this->announcement_date?->format('M d, Y');
            $time = $this->time ? \Carbon\Carbon::parse($this->time)->format('g:i A') : null;

            return trim('Meeting scheduled'.($when ? " on {$when}" : '').($time ? " at {$time}" : '').'.');
        }

        if (filled($this->description)) {
            return Str::limit($this->description, 100);
        }

        return match ($this->purpose) {
            'Reminder' => "Reminder: {$this->title}",
            'Resolution' => 'A resolution has been posted.',
            default => 'A new announcement has been posted.',
        };
    }

    /**
     * Every user who should be notified: staff (Manager/Admin) always, plus
     * the farmers this announcement's audience actually targets.
     *
     * @return Collection<int, int>
     */
    public function resolveRecipientUserIds(): Collection
    {
        $staffIds = User::whereHas('role', fn ($q) => $q->whereIn('name', ['Manager', 'Admin']))->pluck('id');

        if ($this->audience === 'all_members') {
            $farmerUserIds = Farmer::where('status', 'approved')->whereNotNull('account_user_id')->pluck('account_user_id');
        } else {
            $farmerUserIds = $this->recipients()->whereNotNull('account_user_id')->pluck('account_user_id');
        }

        return $staffIds->merge($farmerUserIds)->unique()->values();
    }
}
