<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'land_area',
        'province',
        'municipality',
        'barangay',
        'status',
        'rejection_reason',
        'documents_path',
        'profile_picture',
        'certificate_of_title_path',
        'barangay_certification_path',
        'rsbsa_path',
        'user_id',
        'account_user_id',
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
     * Get the crops grown by the farmer.
     */
    public function crops(): BelongsToMany
    {
        return $this->belongsToMany(Crop::class, 'farmer_crop');
    }

    /**
     * Comma-separated list of the farmer's crop names, for display.
     */
    public function getCropNamesAttribute(): string
    {
        return $this->crops->pluck('name')->implode(', ');
    }

    /**
     * Get the user that registered the farmer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the login account linked to this farmer's membership record.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_user_id');
    }

    /**
     * Get the loan requests submitted on behalf of this farmer.
     */
    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    /**
     * Get the announcements specifically targeted at this farmer.
     */
    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'announcement_recipients');
    }

    /**
     * Get this farmer's CBU (Capital Build-Up) account, if one has been opened yet.
     */
    public function cbu(): HasOne
    {
        return $this->hasOne(Cbu::class);
    }

    /**
     * Get this farmer's recorded harvest payments (the cooperative's cut of
     * their reported harvest income).
     */
    public function harvestPayments(): HasMany
    {
        return $this->hasMany(HarvestPayment::class);
    }

    /**
     * Minimum size required for every farmer's very first CBU contribution:
     * a flat ₱4,000, regardless of land area. Every contribution after the
     * first only needs to clear a flat ₱1,000 minimum, enforced separately
     * where contributions are recorded.
     */
    public function getFirstCbuContributionMinimumAttribute(): int
    {
        return 4000;
    }

    /**
     * Regular Loan CBU eligibility: a farmer needs at least ₱4,000 in their
     * CBU fund to request a Regular Loan at all, and may borrow at most
     * twice their actual CBU balance. Single source of truth shared by the
     * Manager's Loan Request form and the farmer's own Loan Appointment
     * pre-request, so both enforce the identical rule instead of two copies
     * that could drift apart.
     */
    public function getRegularLoanEligibilityAttribute(): array
    {
        $minCbu = 4000;
        $balance = (float) ($this->cbu->balance ?? 0);

        return [
            'cbu_balance' => $balance,
            'min_cbu' => $minCbu,
            'eligible' => $balance >= $minCbu,
            'max_loanable' => $balance * 2,
        ];
    }
}