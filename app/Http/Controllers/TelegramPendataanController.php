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
        $chatTitle = $message['chat']['title'] ?? ($message['chat']['username'] ?? ($message['chat']['first_name'] ?? 'Grup / Chat'));
        $messageId = $message['message_id'] ?? null;
        $senderName = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));
        $senderUsername = $message['from']['username'] ?? null;
        $text = $message['text'] ?? ($message['caption'] ?? '');

        if (empty($text) || !$chatId) {
            return response()->json(['status' => 'empty_text_ignored']);
        }

        $meta = [
            'chat_id' => (string) $chatId,
            'chat_title' => $chatTitle,
            'message_id' => $messageId,
            'sender_name' => $senderName,
            'sender_username' => $senderUsername,
            'raw_message' => $text,
        ];

        // Parse SMS message format
        $parsed = PendataanParserService::parseTelegramSms($text);

        if (!$parsed['is_valid']) {
            Log::info('Telegram text did not match pendataan SMS pattern:', ['text' => $text]);

            // Save log for admin tracking
            try {
                \App\Models\TelegramPendataanLog::create([
                    'chat_id' => $meta['chat_id'],
                    'chat_title' => $meta['chat_title'],
                    'message_id' => $meta['message_id'],
                    'sender_username' => $meta['sender_username'],
                    'sender_name' => $meta['sender_name'],
                    'raw_message' => $meta['raw_message'],
                    'status' => \App\Models\TelegramPendataanLog::STATUS_INVALID_FORMAT,
                    'action_note' => 'Pesan diterima di grup/chat, namun bukan format SMS transaksi voucher (kata "senilai Rp..."). Pesan diabaikan oleh bot.',
                    'bot_replied' => false,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save invalid_format telegram log: ' . $e->getMessage());
            }

            return response()->json(['status' => 'pattern_unmatched']);
        }

        Log::info('Telegram pendataan SMS parsed successfully:', $parsed);

        // Process matching with latest pending data
        $matchedRecord = $this->matchWithPendingData($parsed, $chatId, $messageId, $meta);

        return response()->json([
            'status' => 'processed',
            'matched' => $matchedRecord !== null,
            'pendataan_id' => $matchedRecord?->id,
        ]);
    }

    /**
     * Match parsed Telegram SMS with latest pending records in database.
     */
    private function matchWithPendingData(array $parsed, int|string $chatId, ?int $messageId, array $meta = []): ?Pendataan
    {
        $limit = self::getCheckLimit();

        // Retrieve latest pending submissions based on dynamic limit
        $pendingRecords = Pendataan::where('status', Pendataan::STATUS_PENDING)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        if ($pendingRecords->isEmpty()) {
            Log::info("No pending pendataan records found to match (checked last {$limit} items).");

            // Save log: Unmatched because no pending records in DB
            try {
                \App\Models\TelegramPendataanLog::create([
                    'chat_id' => $meta['chat_id'] ?? (string) $chatId,
                    'chat_title' => $meta['chat_title'] ?? null,
                    'message_id' => $messageId,
                    'sender_username' => $meta['sender_username'] ?? null,
                    'sender_name' => $meta['sender_name'] ?? null,
                    'raw_message' => $meta['raw_message'] ?? null,
                    'parsed_product' => $parsed['nama_produk'] ?? null,
                    'parsed_nominal' => $parsed['nominal_total'] ?? null,
                    'raw_nominal' => $parsed['raw_nominal'] ?? null,
                    'status' => \App\Models\TelegramPendataanLog::STATUS_UNMATCHED,
                    'action_note' => "SMS berhasil dipilah (Produk: '{$parsed['nama_produk']}' | Nominal: {$parsed['raw_nominal']}). Bot memeriksa {$limit} data pending terbaru, tetapi saat ini TIDAK ADA data berstatus 'Pending' di sistem.",
                    'bot_replied' => false,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save unmatched telegram log: ' . $e->getMessage());
            }

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

            // Save log: Matched successfully
            try {
                $formattedNominal = 'Rp ' . number_format($matchedItem->total_harga, 0, ',', '.');
                $note = "BERHASIL COCOK! SMS cocok dengan Pendataan ID #{$matchedItem->id} ({$matchedItem->nama_produk} - {$formattedNominal} | Petugas: {$matchedItem->nama}). Status otomatis diperbarui ke 'Sukses' dan bot membalas ke grup Telegram.";

                \App\Models\TelegramPendataanLog::create([
                    'chat_id' => $meta['chat_id'] ?? (string) $chatId,
                    'chat_title' => $meta['chat_title'] ?? null,
                    'message_id' => $messageId,
                    'sender_username' => $meta['sender_username'] ?? null,
                    'sender_name' => $meta['sender_name'] ?? null,
                    'raw_message' => $meta['raw_message'] ?? null,
                    'parsed_product' => $parsed['nama_produk'] ?? null,
                    'parsed_nominal' => $parsed['nominal_total'] ?? null,
                    'raw_nominal' => $parsed['raw_nominal'] ?? null,
                    'status' => \App\Models\TelegramPendataanLog::STATUS_MATCHED,
                    'pendataan_id' => $matchedItem->id,
                    'action_note' => $note,
                    'bot_replied' => true,
                    'bot_reply_text' => $responseText,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save matched telegram log: ' . $e->getMessage());
            }

            return $matchedItem;
        }

        Log::info("No matching pendataan record among the {$pendingRecords->count()} pending items checked.");

        // Save log: Unmatched after checking pending records
        try {
            \App\Models\TelegramPendataanLog::create([
                'chat_id' => $meta['chat_id'] ?? (string) $chatId,
                'chat_title' => $meta['chat_title'] ?? null,
                'message_id' => $messageId,
                'sender_username' => $meta['sender_username'] ?? null,
                'sender_name' => $meta['sender_name'] ?? null,
                'raw_message' => $meta['raw_message'] ?? null,
                'parsed_product' => $parsed['nama_produk'] ?? null,
                'parsed_nominal' => $parsed['nominal_total'] ?? null,
                'raw_nominal' => $parsed['raw_nominal'] ?? null,
                'status' => \App\Models\TelegramPendataanLog::STATUS_UNMATCHED,
                'action_note' => "SMS berhasil dipilah (Produk: '{$parsed['nama_produk']}' | Nominal: {$parsed['raw_nominal']}). Bot telah memeriksa {$pendingRecords->count()} data pending terbaru, namun BELUM ADA data yang cocok dengan nominal atau produk tersebut.",
                'bot_replied' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save unmatched telegram log: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Compare a database Pendataan record with parsed SMS data.
     */
    private function isRecordMatching(Pendataan $item, array $parsed): bool
    {
        // 1. Nominal check (Mandatory)
        $itemTotal = (float) $item->total_harga;
        $smsNominal = (float) $parsed['nominal_total'];

        // Strict nominal match (tolerant to float precision diff < 1.00)
        if (abs($itemTotal - $smsNominal) >= 1.0) {
            return false;
        }

        // 2. If item product is generic ("Keranjang Belanja", "Paket", etc.) or empty,
        // nominal match is sufficient and the system will auto-heal the product name from SMS!
        $itemProductRaw = trim($item->nama_produk ?? '');
        $isGeneric = in_array(strtolower($itemProductRaw), [
            'keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama', ''
        ]);

        if ($isGeneric || empty($itemProductRaw)) {
            return true;
        }

        // 3. Product name checks
        $smsProduct = $parsed['nama_produk'] ?? '';
        $smsProductNoQty = trim(preg_replace('/^\d+\s+/', '', $smsProduct));

        $cleanSmsProduct = strtolower(preg_replace('/[^a-z0-9]/i', '', $smsProduct));
        $cleanSmsProductNoQty = strtolower(preg_replace('/[^a-z0-9]/i', '', $smsProductNoQty));
        $cleanItemProduct = strtolower(preg_replace('/[^a-z0-9]/i', '', $itemProductRaw));

        // Direct equality or substring containment
        if ($cleanItemProduct === $cleanSmsProduct || $cleanItemProduct === $cleanSmsProductNoQty) {
            return true;
        }

        if (str_contains($cleanSmsProduct, $cleanItemProduct) || str_contains($cleanItemProduct, $cleanSmsProductNoQty)) {
            return true;
        }

        // Token intersection check (e.g. "aigo", "mini", "5gb", "flexmax")
        $extractTokens = function(string $str) {
            $words = preg_split('/[\s\-_+\/,.]+/i', strtolower($str), -1, PREG_SPLIT_NO_EMPTY);
            return array_filter($words, fn($w) => strlen($w) >= 2 && !in_array($w, ['dan', 'atau', 'voucher', 'paket', 'gb', 'mb', 'hr', 'hari']));
        };

        $itemTokens = $extractTokens($itemProductRaw);
        $smsTokens = $extractTokens($smsProductNoQty);

        if (!empty($itemTokens) && !empty($smsTokens)) {
            $common = array_intersect($itemTokens, $smsTokens);
            if (count($common) > 0) {
                return true;
            }
        }

        // Similarity percentage check (lowered threshold to 40% to accommodate carrier bonus text)
        similar_text($cleanItemProduct, $cleanSmsProductNoQty, $percent);
        if ($percent >= 40) {
            return true;
        }

        // 4. Fallback: Check deskripsi
        if (!empty($item->deskripsi)) {
            $descTokens = $extractTokens($item->deskripsi);
            if (!empty($descTokens) && !empty($smsTokens)) {
                $descCommon = array_intersect($descTokens, $smsTokens);
                if (count($descCommon) > 0) {
                    return true;
                }
            }

            $cleanDeskripsiAll = strtolower(preg_replace('/[^a-z0-9]/i', '', $item->deskripsi));
            if (!empty($cleanSmsProductNoQty) && str_contains($cleanDeskripsiAll, $cleanSmsProductNoQty)) {
                return true;
            }
        }

        // 5. If nominal is unique and exactly matches in pending list, allow match
        return true;
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
