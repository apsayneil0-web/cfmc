<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    protected $fillable = [
        'crop_type',
        'full_name',
        'first_name',
        'middle_initial',
        'last_name',
        'suffix',
        'barangay',
        'municipality',
        'province',
        'contact_number',
        'land_area',
        'documents',
        'status'
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function crop()
    {
        return $this->belongsTo(Crop::class, 'crop_type');
    }
}
