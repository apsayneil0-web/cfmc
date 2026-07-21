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
