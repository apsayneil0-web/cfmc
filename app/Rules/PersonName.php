<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A person's name: letters (including ñ and accented letters), spaces,
 * periods, hyphens and apostrophes — e.g. "Ma. Cristina", "Dela Cruz-Peña",
 * "D'Souza". Must start with a letter. Mirrors the data-input="name" typing
 * filter in resources/js/input-filters.js.
 */
class PersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match("/^\pL[\pL\s.'\-]*$/u", $value)) {
            $fail('The :attribute may only contain letters, spaces, periods, hyphens and apostrophes.');
        }
    }
}
