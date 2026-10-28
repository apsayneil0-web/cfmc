<?php

if (! function_exists('peso')) {
    /**
     * Format an amount as Philippine peso currency (e.g. "₱1,234.56").
     *
     * Uses the literal UTF-8 ₱ character rather than the &#8369; HTML entity
     * so it renders correctly even when passed through a Blade component
     * prop that echoes it with {{ }} (which escapes "&" and breaks the
     * entity into literal "&#8369;" text).
     */
    function peso(float|int|string $amount, int $decimals = 2): string
    {
        return '₱'.number_format((float) $amount, $decimals);
    }
}

if (! function_exists('phone')) {
    /**
     * Format a Philippine mobile number for display ("0976 356 6432").
     * Anything that isn't an 11-digit 09XX number is returned unchanged.
     */
    function phone(?string $number): string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        return preg_match('/^(09\d{2})(\d{3})(\d{4})$/', $digits, $m)
            ? "{$m[1]} {$m[2]} {$m[3]}"
            : (string) $number;
    }
}
