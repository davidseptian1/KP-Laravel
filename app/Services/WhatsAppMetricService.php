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
        $to = $this->normalizeNumber($to);
        $sender = $this->normalizeNumber($sender);

        if (!$baseUrl || !$to || !$message) {
            Log::warning('WhatsApp Metric API skipped', [
                'base_url' => $baseUrl,
                'to' => $to,
                'has_message' => (bool) $message,
            ]);
            return;
        }

        try {
            $request = Http::timeout(10)->asForm();
            if ($token) {
                $request = $request->withToken($token);
            }
            if ($apiKey) {
                $request = $request->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'api_key' => $apiKey,
                ]);
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
                    'sender' => $sender,
                    'endpoint' => rtrim($baseUrl, '/') . $endpoint,
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

    private function normalizeNumber(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return $digits;
    }
}
