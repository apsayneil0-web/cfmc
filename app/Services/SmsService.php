<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single seam for texting non-member farmers, who have no user account and
 * so can't receive in-app Notification rows. Backed by Semaphore
 * (see config/services.php 'semaphore'); sends are logged instead if the
 * API key isn't configured, so nothing breaks and call sites don't change.
 */
class SmsService
{
    public function send(string $contactNumber, string $message): void
    {
        $apiKey = config('services.semaphore.api_key');

        if (! $apiKey) {
            Log::info('SMS not sent (Semaphore not configured yet)', [
                'to' => $contactNumber,
                'message' => $message,
            ]);

            return;
        }

        $params = [
            'apikey' => $apiKey,
            'number' => $contactNumber,
            'message' => $message,
        ];

        // Omit sendername entirely unless one's configured — Semaphore rejects
        // any value that isn't an approved sender for the account, including
        // the shared "Semaphore" name on accounts that haven't requested it.
        if ($senderName = config('services.semaphore.sender_name')) {
            $params['sendername'] = $senderName;
        }

        $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', $params);

        if ($response->failed()) {
            Log::error('Semaphore SMS send failed', [
                'to' => $contactNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
