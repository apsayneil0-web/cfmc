<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Single seam for texting non-member farmers, who have no user account and
 * so can't receive in-app Notification rows. Semaphore isn't configured yet
 * (see config/services.php 'semaphore') — until it is, sends are just logged
 * so nothing breaks and the call sites don't need to change later.
 */
class SmsService
{
    public function send(string $contactNumber, string $message): void
    {
        if (! config('services.semaphore.api_key')) {
            Log::info('SMS not sent (Semaphore not configured yet)', [
                'to' => $contactNumber,
                'message' => $message,
            ]);

            return;
        }

        // TODO: call the Semaphore SMS API once services.semaphore.api_key is set.
    }
}
