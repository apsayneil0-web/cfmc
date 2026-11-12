<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return self::findByName($name) ?? self::create(['name' => trim($name)]);
    }

    public static function findByName(string $name): ?self
    {
        return self::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->first();
    }

    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class);
    }
}