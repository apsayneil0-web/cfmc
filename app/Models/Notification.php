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

    /**
     * Icon + short eyebrow label shown in the notification bell, keyed by type.
     */
    public static function typeDisplay(string $type): array
    {
        return match ($type) {
            'meeting' => ['icon' => '📅', 'label' => 'New Meeting Announcement'],
            'reminder' => ['icon' => '⏰', 'label' => 'Reminder'],
            'resolution' => ['icon' => '✅', 'label' => 'Resolution Posted'],
            default => ['icon' => '📢', 'label' => 'New Announcement'],
        };
    }
}
