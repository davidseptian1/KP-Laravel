<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppMetricService
{
    public function sendText(string $to, string $message): void
    {
        $baseUrl = config('whatsapp.base_url');
        $endpoint = config('whatsapp.endpoint', '/messages');
        $token = config('whatsapp.token');
        $apiKey = config('whatsapp.api_key');
        $sender = config('whatsapp.sender');

        if (!$baseUrl || !$to || !$message) {
            Log::warning('WhatsApp Metric API skipped', [
                'base_url' => $baseUrl,
                'to' => $to,
                'has_message' => (bool) $message,
            ]);
            return;
        }

        try {
            $request = Http::timeout(10);
            if ($token) {
                $request = $request->withToken($token);
            }

            $payload = [
                'api_key' => $apiKey,
                'sender' => $sender,
                'number' => $to,
                'message' => $message,
            ];

            $response = $request->post(rtrim($baseUrl, '/') . $endpoint, $payload);

            if ($response->failed()) {
                Log::warning('WhatsApp Metric API failed response', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp Metric API failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendToAdmins(string $message): void
    {
        $numbers = config('whatsapp.admin_numbers', []);
        foreach ($numbers as $number) {
            $this->sendText($number, $message);
        }
    }
}
