<?php

namespace App\Http\Controllers;

use App\Models\Pendataan;
use App\Models\SosmedSetting;
use App\Services\PendataanParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPendataanController extends Controller
{
    /**
     * Get configured Bot Token.
     */
    public static function getBotToken(): string
    {
        $tokenFromSetting = SosmedSetting::getByKey('pendataan_telegram_bot_token');
        if (!empty($tokenFromSetting)) {
            return trim($tokenFromSetting);
        }

        return config('services.telegram_pendataan.bot_token')
            ?: env('TELEGRAM_PENDATAAN_BOT_TOKEN', '8826086655:AAHRHD8c7i0IUV9Yrd-sOEe81boc7ls9TwM');
    }

    /**
     * Get dynamic check limit (default 5).
     */
    public static function getCheckLimit(): int
    {
        $limit = (int) SosmedSetting::getByKey('pendataan_telegram_check_limit');
        if ($limit > 0) {
            return $limit;
        }

        return (int) (config('services.telegram_pendataan.check_limit') ?: 5);
    }

    /**
     * Handle incoming webhook updates from Telegram Bot.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('--- TELEGRAM PENDATAAN WEBHOOK PAYLOAD ---', [
            'has_message' => isset($update['message']),
            'chat' => $update['message']['chat'] ?? null,
            'text' => $update['message']['text'] ?? null,
        ]);

        if (!isset($update['message'])) {
            return response()->json(['status' => 'no_message_ignored']);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;
        $text = $message['text'] ?? ($message['caption'] ?? '');

        if (empty($text) || !$chatId) {
            return response()->json(['status' => 'empty_text_ignored']);
        }

        // Parse SMS message format
        $parsed = PendataanParserService::parseTelegramSms($text);

        if (!$parsed['is_valid']) {
            Log::info('Telegram text did not match pendataan SMS pattern:', ['text' => $text]);
            return response()->json(['status' => 'pattern_unmatched']);
        }

        Log::info('Telegram pendataan SMS parsed successfully:', $parsed);

        // Process matching with latest pending data
        $matchedRecord = $this->matchWithPendingData($parsed, $chatId, $messageId);

        return response()->json([
            'status' => 'processed',
            'matched' => $matchedRecord !== null,
            'pendataan_id' => $matchedRecord?->id,
        ]);
    }

    /**
     * Match parsed Telegram SMS with latest pending records in database.
     */
    private function matchWithPendingData(array $parsed, int|string $chatId, ?int $messageId): ?Pendataan
    {
        $limit = self::getCheckLimit();

        // Retrieve latest pending submissions based on dynamic limit
        $pendingRecords = Pendataan::where('status', Pendataan::STATUS_PENDING)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        if ($pendingRecords->isEmpty()) {
            Log::info("No pending pendataan records found to match (checked last {$limit} items).");
            return null;
        }

        $matchedItem = null;

        foreach ($pendingRecords as $item) {
            if ($this->isRecordMatching($item, $parsed)) {
                $matchedItem = $item;
                break;
            }
        }

        if ($matchedItem) {
            // Update status to 'sukses'
            $matchedItem->status = Pendataan::STATUS_SUKSES;

            // Auto-heal product name if it was erroneously saved as "Keranjang Belanja" or generic
            $isGeneric = in_array(strtolower(trim($matchedItem->nama_produk ?? '')), [
                'keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama', ''
            ]);

            if ($isGeneric) {
                $smsProductNoQty = trim(preg_replace('/^\d+\s+/', '', $parsed['nama_produk'] ?? ''));
                if (!empty($smsProductNoQty)) {
                    $matchedItem->nama_produk = $smsProductNoQty;
                }
            }

            $matchedItem->save();

            Log::info("Pendataan #{$matchedItem->id} marked as SUKSES via Telegram match.");

            // Format telegram response
            // nama_produk:
            // nomimal :
            // Sudah sesuai ( icon centang )
            $responseText = "nama_produk: {$parsed['nama_produk']}\n" .
                            "nomimal : {$parsed['raw_nominal']}\n" .
                            "Sudah sesuai ✅";

            self::sendMessage($chatId, $responseText, $messageId);

            return $matchedItem;
        }

        Log::info("No matching pendataan record among the {$pendingRecords->count()} pending items checked.");
        return null;
    }

    /**
     * Compare a database Pendataan record with parsed SMS data.
     */
    private function isRecordMatching(Pendataan $item, array $parsed): bool
    {
        // 1. Nominal check
        $itemTotal = (float) $item->total_harga;
        $smsNominal = (float) $parsed['nominal_total'];

        // Strict nominal match (tolerant to float precision diff < 1.00)
        if (abs($itemTotal - $smsNominal) >= 1.0) {
            return false;
        }

        // 2. Product name check
        $smsProduct = $parsed['nama_produk'] ?? '';
        $smsProductNoQty = trim(preg_replace('/^\d+\s+/', '', $smsProduct));

        $cleanSmsProduct = strtolower(preg_replace('/[^a-z0-9]/i', '', $smsProduct));
        $cleanSmsProductNoQty = strtolower(preg_replace('/[^a-z0-9]/i', '', $smsProductNoQty));

        $cleanItemProduct = strtolower(preg_replace('/[^a-z0-9]/i', '', $item->nama_produk ?? ''));

        $isGeneric = in_array(strtolower(trim($item->nama_produk ?? '')), [
            'keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama', ''
        ]);

        // Direct item product match (if not generic)
        if (!$isGeneric && !empty($cleanItemProduct)) {
            if ($cleanItemProduct === $cleanSmsProduct || $cleanItemProduct === $cleanSmsProductNoQty) {
                return true;
            }

            if (str_contains($cleanSmsProduct, $cleanItemProduct) || str_contains($cleanItemProduct, $cleanSmsProductNoQty)) {
                return true;
            }

            similar_text($cleanItemProduct, $cleanSmsProductNoQty, $percent);
            if ($percent >= 70) {
                return true;
            }
        }

        // 3. Fallback: Check deskripsi
        if (!empty($item->deskripsi)) {
            $parsedDeskripsi = PendataanParserService::parse($item->deskripsi);
            if (!empty($parsedDeskripsi['nama_produk'])) {
                $cleanDeskripsiProduct = strtolower(preg_replace('/[^a-z0-9]/i', '', $parsedDeskripsi['nama_produk']));
                if ($cleanDeskripsiProduct === $cleanSmsProductNoQty || str_contains($cleanSmsProduct, $cleanDeskripsiProduct)) {
                    return true;
                }
                similar_text($cleanDeskripsiProduct, $cleanSmsProductNoQty, $pct);
                if ($pct >= 70) {
                    return true;
                }
            }

            // Keyword check inside full deskripsi
            $cleanDeskripsiAll = strtolower(preg_replace('/[^a-z0-9]/i', '', $item->deskripsi));
            if (!empty($cleanSmsProductNoQty) && str_contains($cleanDeskripsiAll, $cleanSmsProductNoQty)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send message to Telegram Chat / Group with optional reply.
     */
    public static function sendMessage(int|string $chatId, string $text, ?int $replyToMessageId = null): bool
    {
        $botToken = self::getBotToken();
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($replyToMessageId) {
            $payload['reply_to_message_id'] = $replyToMessageId;
            $payload['allow_sending_without_reply'] = true;
        }

        try {
            $response = Http::timeout(10)->post($url, $payload);
            if ($response->successful()) {
                Log::info("Telegram message successfully sent to chat {$chatId}.");
                return true;
            }

            Log::error("Failed to send Telegram message: " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Exception sending Telegram message: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Helper endpoint to set or check webhook status.
     */
    public function setupWebhook(Request $request): JsonResponse
    {
        $botToken = self::getBotToken();
        $webhookUrl = $request->input('url', url('/api/telegram/pendataan-webhook'));

        $url = "https://api.telegram.org/bot{$botToken}/setWebhook";
        $response = Http::post($url, ['url' => $webhookUrl]);

        return response()->json([
            'status' => $response->successful() ? 'success' : 'failed',
            'webhook_url' => $webhookUrl,
            'telegram_response' => $response->json(),
        ]);
    }

    /**
     * Helper endpoint to get current webhook info.
     */
    public function getWebhookInfo(): JsonResponse
    {
        $botToken = self::getBotToken();
        $url = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
        $response = Http::get($url);

        return response()->json($response->json());
    }
}
