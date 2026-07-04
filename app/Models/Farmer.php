<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'documents_path',
        'user_id',
    ];

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