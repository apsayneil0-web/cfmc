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

        // A text that can't be delivered (no internet, provider down) must
        // never crash the page that triggered it — the approval, OTP or
        // announcement has already been saved by then. Log and carry on.
        try {
            $response = Http::asForm()->timeout(10)->post('https://www.iprogsms.com/api/v1/sms_messages', [
                'api_token' => $apiToken,
                'phone_number' => $contactNumber,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('IPROG SMS send failed (could not reach provider)', [
                'to' => $contactNumber,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if ($response->failed() || $response->json('status') !== 200) {
            Log::error('IPROG SMS send failed', [
                'to' => $contactNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}
