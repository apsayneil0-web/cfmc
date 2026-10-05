<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single seam for texting farmers (credentials, OTPs, announcements, and
 * non-member contacts). Backed by IPROG SMS (see config/services.php 'iprog');
 * sends are logged instead if the API token isn't configured.
 */
class SmsService
{
    public function send(string $contactNumber, string $message): void
    {
        $apiToken = config('services.iprog.api_token');

        if (! $apiToken) {
            Log::info('SMS not sent (IPROG not configured yet)', [
                'to' => $contactNumber,
                'message' => $message,
            ]);

            return;
        }

        $response = Http::asForm()->post('https://www.iprogsms.com/api/v1/sms_messages', [
            'api_token' => $apiToken,
            'phone_number' => $contactNumber,
            'message' => $message,
        ]);

        if ($response->failed() || $response->json('status') !== 200) {
            Log::error('IPROG SMS send failed', [
                'to' => $contactNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
