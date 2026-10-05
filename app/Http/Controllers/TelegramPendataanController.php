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
            'has_channel_post' => isset($update['channel_post']),
            'update' => $update,
        ]);

        // Support standard messages, channel posts, and edited messages
        $message = $update['message']
            ?? ($update['channel_post']
            ?? ($update['edited_message']
            ?? ($update['edited_channel_post'] ?? null)));

        if (!$message) {
            return response()->json(['status' => 'no_message_ignored']);
        }

        $chatId = $message['chat']['id'] ?? null;
        $chatTitle = $message['chat']['title']
            ?? ($message['chat']['username']
            ?? ($message['chat']['first_name'] ?? 'Grup / Saluran'));
        $messageId = $message['message_id'] ?? null;
        
        $senderFirstName = $message['from']['first_name'] ?? '';
        $senderLastName = $message['from']['last_name'] ?? '';
        $senderName = trim("{$senderFirstName} {$senderLastName}")
            ?: ($message['author_signature'] ?? ($chatTitle ?? 'Sender'));
            
        $senderUsername = $message['from']['username']
            ?? ($message['sender_chat']['username'] ?? null);
            
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
     * Handle direct SMS forwarded via HTTP Webhook (e.g. from Android SMS Forwarder apps, MacroDroid, Tasker).
     * Endpoint: POST /api/sms/pendataan-webhook
     */
    public function handleDirectSms(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('--- DIRECT SMS FORWARDER WEBHOOK PAYLOAD ---', ['payload' => $payload]);

        // Extract text from common SMS forwarder parameter names
        $text = $request->input('message')
            ?? $request->input('text')
            ?? $request->input('msg')
            ?? $request->input('body')
            ?? $request->input('content')
            ?? $request->input('sms')
            ?? $request->getContent();

        if (empty($text) || !is_string($text)) {
            return response()->json(['status' => 'error', 'message' => 'Teks SMS tidak ditemukan pada request.'], 400);
        }

        $sender = $request->input('from') ?? $request->input('sender') ?? $request->input('title') ?? 'Android SMS Forwarder';

        $meta = [
            'chat_id' => 'direct_http_forwarder',
            'chat_title' => 'HTTP SMS Forwarder',
            'message_id' => null,
            'sender_name' => (string) $sender,
            'sender_username' => 'android_forwarder',
            'raw_message' => trim($text),
        ];

        $parsed = PendataanParserService::parseTelegramSms($text);

        if (!$parsed['is_valid']) {
            Log::info('Direct SMS did not match pendataan SMS pattern:', ['text' => $text]);

            try {
                \App\Models\TelegramPendataanLog::create([
                    'chat_id' => $meta['chat_id'],
                    'chat_title' => $meta['chat_title'],
                    'message_id' => null,
                    'sender_username' => $meta['sender_username'],
                    'sender_name' => $meta['sender_name'],
                    'raw_message' => $meta['raw_message'],
                    'status' => \App\Models\TelegramPendataanLog::STATUS_INVALID_FORMAT,
                    'action_note' => 'Pesan diterima via HTTP Webhook SMS Forwarder, namun format teks bukan SMS transaksi voucher ("senilai Rp...").',
                    'bot_replied' => false,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save invalid direct SMS log: ' . $e->getMessage());
            }

            return response()->json(['status' => 'pattern_unmatched'], 422);
        }

        $matchedRecord = $this->matchWithPendingData($parsed, $meta['chat_id'], null, $meta);

        return response()->json([
            'status' => 'processed',
            'matched' => $matchedRecord !== null,
            'pendataan_id' => $matchedRecord?->id,
            'parsed' => $parsed,
        ]);
    }

    /**
     * Match parsed Telegram SMS with latest pending records in database.
     */
    private function matchWithPendingData(array $parsed, int|string $chatId, ?int $messageId, array $meta = []): ?Pendataan
    {
        $limit = max(self::getCheckLimit(), 50);
        $smsNominal = (float) ($parsed['nominal_total'] ?? 0);

        // Check if there are ANY pending records in the system
        $totalPendingCount = Pendataan::where('status', Pendataan::STATUS_PENDING)->count();

        if ($totalPendingCount === 0) {
            Log::info("No pending pendataan records found in system to match.");

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
                    'action_note' => "SMS berhasil dipilah (Produk: '{$parsed['nama_produk']}' | Nominal: {$parsed['raw_nominal']}). Namun saat ini TIDAK ADA data berstatus 'Pending' di sistem web.",
                    'bot_replied' => false,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to save unmatched telegram log: ' . $e->getMessage());
            }

            return null;
        }

        $matchedItem = null;

        // 1. Direct search by exact matching nominal in pending status (FIFO order: oldest pending first)
        if ($smsNominal > 0) {
            $exactNominalRecords = Pendataan::where('status', Pendataan::STATUS_PENDING)
                ->where(function ($q) use ($smsNominal) {
                    $q->where('total_harga', $smsNominal)
                      ->orWhereBetween('total_harga', [$smsNominal - 1.0, $smsNominal + 1.0]);
                })
                ->orderBy('created_at', 'asc')
                ->limit(20)
                ->get();

            foreach ($exactNominalRecords as $item) {
                if ($this->isRecordMatching($item, $parsed)) {
                    $matchedItem = $item;
                    break;
                }
            }
        }

        // 2. Fallback: Search among the most recent pending submissions (up to $limit items)
        if (!$matchedItem) {
            $pendingRecords = Pendataan::where('status', Pendataan::STATUS_PENDING)
                ->latest('created_at')
                ->limit($limit)
                ->get();

            foreach ($pendingRecords as $item) {
                if ($this->isRecordMatching($item, $parsed)) {
                    $matchedItem = $item;
                    break;
                }
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

    /**
     * Re-sync unmatched Telegram logs against current pending Pendataan records.
     */
    public static function syncUnmatchedLogs(?int $hoursBack = 48): int
    {
        $cutoff = now()->subHours($hoursBack);

        $unmatchedLogs = \App\Models\TelegramPendataanLog::where('status', \App\Models\TelegramPendataanLog::STATUS_UNMATCHED)
            ->where('created_at', '>=', $cutoff)
            ->whereNotNull('parsed_nominal')
            ->orderBy('id', 'asc')
            ->get();

        $matchedCount = 0;
        $controller = new self();

        foreach ($unmatchedLogs as $log) {
            $smsNominal = (float) $log->parsed_nominal;
            if ($smsNominal <= 0) continue;

            $parsed = [
                'is_valid' => true,
                'nama_produk' => $log->parsed_product ?? '',
                'nominal_total' => $smsNominal,
                'raw_nominal' => $log->raw_nominal ?? ('Rp' . number_format($smsNominal, 0, '', '')),
            ];

            // Search pending records
            $pendingRecords = Pendataan::where('status', Pendataan::STATUS_PENDING)
                ->where(function ($q) use ($smsNominal) {
                    $q->where('total_harga', $smsNominal)
                      ->orWhereBetween('total_harga', [$smsNominal - 1.0, $smsNominal + 1.0]);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $matchedItem = null;
            foreach ($pendingRecords as $item) {
                if ($controller->isRecordMatching($item, $parsed)) {
                    $matchedItem = $item;
                    break;
                }
            }

            if ($matchedItem) {
                $matchedItem->status = Pendataan::STATUS_SUKSES;

                $isGeneric = in_array(strtolower(trim($matchedItem->nama_produk ?? '')), [
                    'keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama', ''
                ]);

                if ($isGeneric && !empty($parsed['nama_produk'])) {
                    $smsProductNoQty = trim(preg_replace('/^\d+\s+/', '', $parsed['nama_produk']));
                    if (!empty($smsProductNoQty)) {
                        $matchedItem->nama_produk = $smsProductNoQty;
                    }
                }

                $matchedItem->save();

                $responseText = "nama_produk: {$parsed['nama_produk']}\n" .
                                "nomimal : {$parsed['raw_nominal']}\n" .
                                "Sudah sesuai ✅";

                $replied = false;
                if (!empty($log->chat_id)) {
                    $replied = self::sendMessage($log->chat_id, $responseText, $log->message_id);
                }

                $formattedNominal = 'Rp ' . number_format($matchedItem->total_harga, 0, ',', '.');
                $note = "BERHASIL DISINKRONKAN! Cocok dengan Pendataan ID #{$matchedItem->id} ({$matchedItem->nama_produk} - {$formattedNominal} | Petugas: {$matchedItem->nama}). Status otomatis diperbarui ke 'Sukses'.";

                $log->update([
                    'status' => \App\Models\TelegramPendataanLog::STATUS_MATCHED,
                    'pendataan_id' => $matchedItem->id,
                    'action_note' => $note,
                    'bot_replied' => $replied,
                    'bot_reply_text' => $responseText,
                ]);

                $matchedCount++;
            }
        }

        return $matchedCount;
    }

    /**
     * Check if a newly created Pendataan record matches an unmatched SMS log.
     */
    public static function checkNewlyCreatedPendataan(Pendataan $pendingItem): bool
    {
        $nominal = (float) $pendingItem->total_harga;
        if ($nominal <= 0) return false;

        $unmatchedLog = \App\Models\TelegramPendataanLog::where('status', \App\Models\TelegramPendataanLog::STATUS_UNMATCHED)
            ->where('created_at', '>=', now()->subHours(24))
            ->where(function ($q) use ($nominal) {
                $q->where('parsed_nominal', $nominal)
                  ->orWhereBetween('parsed_nominal', [$nominal - 1.0, $nominal + 1.0]);
            })
            ->orderBy('id', 'asc')
            ->first();

        if ($unmatchedLog) {
            $parsed = [
                'nama_produk' => $unmatchedLog->parsed_product ?? $pendingItem->nama_produk,
                'raw_nominal' => $unmatchedLog->raw_nominal ?? ('Rp' . number_format($nominal, 0, '', '')),
            ];

            $pendingItem->status = Pendataan::STATUS_SUKSES;

            $isGeneric = in_array(strtolower(trim($pendingItem->nama_produk ?? '')), [
                'keranjang belanja', 'keranjang', 'paket', '(1) paket', 'produk', 'produk tanpa nama', ''
            ]);

            if ($isGeneric && !empty($parsed['nama_produk'])) {
                $smsProductNoQty = trim(preg_replace('/^\d+\s+/', '', $parsed['nama_produk']));
                if (!empty($smsProductNoQty)) {
                    $pendingItem->nama_produk = $smsProductNoQty;
                }
            }

            $pendingItem->save();

            $responseText = "nama_produk: {$parsed['nama_produk']}\n" .
                            "nomimal : {$parsed['raw_nominal']}\n" .
                            "Sudah sesuai ✅";

            $replied = false;
            if (!empty($unmatchedLog->chat_id)) {
                $replied = self::sendMessage($unmatchedLog->chat_id, $responseText, $unmatchedLog->message_id);
            }

            $formattedNominal = 'Rp ' . number_format($pendingItem->total_harga, 0, ',', '.');
            $note = "BERHASIL COCOK (AUTO-SYNC)! SMS yang masuk sebelumnya cocok dengan transaksi baru ID #{$pendingItem->id} ({$pendingItem->nama_produk} - {$formattedNominal} | Petugas: {$pendingItem->nama}). Status diperbarui ke 'Sukses'.";

            $unmatchedLog->update([
                'status' => \App\Models\TelegramPendataanLog::STATUS_MATCHED,
                'pendataan_id' => $pendingItem->id,
                'action_note' => $note,
                'bot_replied' => $replied,
                'bot_reply_text' => $responseText,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Handle manual sync request from UI.
     */
    public function manualSync(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessPendataan()) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyinkronkan data.');
        }

        $matchedCount = self::syncUnmatchedLogs(48);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'matched' => $matchedCount,
                'message' => $matchedCount > 0
                    ? "Berhasil menyinkronkan {$matchedCount} transaksi dengan SMS Telegram!"
                    : "Tidak ada transaksi pending yang cocok dengan riwayat SMS Telegram saat ini.",
            ]);
        }

        if ($matchedCount > 0) {
            return back()->with('success', "⚡ Sinkronisasi Berhasil! {$matchedCount} transaksi pending berhasil dicocokkan dengan SMS Telegram dan status telah diperbarui ke 'Sukses ✅'.");
        }

        return back()->with('info', "Sinkronisasi selesai. Saat ini tidak ditemukan transaksi pending yang nominalnya cocok dengan riwayat SMS Telegram.");
    }
}
