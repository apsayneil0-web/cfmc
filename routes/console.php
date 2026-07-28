<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keeps loan interest, grace-period penalties, and delinquency flags current
// even if no one opens Loan Management that day. Requires the server's cron
// to call `php artisan schedule:run` every minute (standard Laravel setup).
Schedule::command('loans:process-delinquency')->daily();
