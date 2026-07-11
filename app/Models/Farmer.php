<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'suffix',
        'contact_number',
        'crop_id',
        'land_area',
        'province',
        'municipality',
        'barangay',
        'status',
        'rejection_reason',
        'documents_path',
        'user_id',
    ];

    /**
     * Capitalize the first letter of each word in the first name.
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = $value !== null ? Str::title(trim($value)) : $value;
    }

    /**
     * Capitalize the middle initial and ensure it ends with a period (e.g. "D.").
     */
    public function setMiddleInitialAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['middle_initial'] = $value;
            return;
        }

        $initial = strtoupper(rtrim(trim($value), '.'));
        $this->attributes['middle_initial'] = $initial !== '' ? $initial . '.' : $initial;
    }

    /**
     * Capitalize the first letter of each word in the last name.
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $value !== null ? Str::title(trim($value)) : $value;
    }

    /**
     * Get the farmer's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->first_name, $this->middle_initial, $this->last_name, $this->suffix];

        return implode(' ', array_filter($parts, fn ($part) => filled($part)));
    }

    /**
     * Get the crop that belongs to the farmer.
     */
    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    /**
     * Get the user that registered the farmer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}