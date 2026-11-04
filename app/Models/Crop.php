<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Sentinel value for the "+ Other" option on Crop dropdowns — never a
     * real crop_id, just a signal to the controller to resolve/create one
     * from the accompanying free-text name instead.
     */
    public const OTHER_OPTION = '__other__';

    /**
     * Finds a crop by name case-insensitively (so "corn" and "Corn" don't
     * become separate records), or creates a new one if none matches.
     */
    public static function findOrCreateByName(string $name): self
    {
        $name = trim($name);

        return self::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? self::create(['name' => $name]);
    }
}