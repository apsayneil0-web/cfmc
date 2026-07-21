<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'profile_picture',
        'date_of_birth',
        'age',
        'gender',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Capitalize the first letter of each word in the first name.
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = $value !== null ? Str::title(trim($value)) : $value;
    }

    /**
     * Capitalize the first letter of each word in the middle name.
     */
    public function setMiddleNameAttribute($value): void
    {
        $this->attributes['middle_name'] = $value !== null ? Str::title(trim($value)) : $value;
    }

    /**
     * Capitalize the first letter of each word in the last name.
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $value !== null ? Str::title(trim($value)) : $value;
    }

    /**
     * Get the staff member's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->first_name, $this->middle_name, $this->last_name];

        return implode(' ', array_filter($parts, fn ($part) => filled($part)));
    }

    /**
     * Get the login account linked to this staff record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Proxy the linked account's email; staff has no email column of its own
     * to avoid duplicating users.email.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }
}
