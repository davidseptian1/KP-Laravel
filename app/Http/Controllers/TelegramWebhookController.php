<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Reimburse;
use App\Models\User;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();

        // Mencatat semua payload (data) dari Telegram ke log
        \Illuminate\Support\Facades\Log::info('--- TELEGRAM WEBHOOK PAYLOAD ---', $update);

        // Cek jika update berupa callback_query (tombol diklik)
        if (isset($update['callback_query'])) {
            $callbackQuery = $update['callback_query'];
            $callbackQueryId = $callbackQuery['id'];
            $data = $callbackQuery['data'] ?? '';
            $message = $callbackQuery['message'] ?? [];
            $chatId = $message['chat']['id'] ?? null;
            $messageId = $message['message_id'] ?? null;
            
            // Validasi: Pastikan tombol hanya bisa diklik dari Chat ID yang ditentukan
            // di environment, untuk keamanan (atau bisa dilewati jika butuh public access).
            $allowedChatId = env('TELEGRAM_CHAT_ID');

            // Mencatat perbandingan Chat ID ke log
            \Illuminate\Support\Facades\Log::info('--- CEK CHAT ID ---', [
                'ID_Dari_Telegram' => $chatId,
                'ID_Di_Env_Server' => $allowedChatId,
                'Apakah_Sama' => ((string)$chatId === (string)$allowedChatId) ? 'YA' : 'TIDAK'
            ]);

            if ((string)$chatId !== (string)$allowedChatId) {
                return response()->json(['status' => 'unauthorized chat']);
            }

            // Pola: reimburse_approve_{id}, reimburse_reject_{id}, atau reimburse_edit_{id}
            if (preg_match('/^reimburse_(approve|reject|edit)_(\d+)$/', $data, $matches)) {
                $action = $matches[1]; // 'approve', 'reject', atau 'edit'
                $id = $matches[2];

                $reimburse = Reimburse::find($id);

                if (!$reimburse) {
                    $this->answerCallbackQuery($callbackQueryId, 'Data reimburse tidak ditemukan.');
                    return response()->json(['status' => 'not found']);
                }

                if ($action === 'edit') {
                    // Kembalikan tombol Approve & Reject
                    // Hilangkan teks "[STATUS: Telah ...]" dari pesan
                    $originalText = preg_replace('/\n\n\*\[STATUS: Telah .*? melalui Bot\]\*/', '', $message['text']);
                    
                    $this->editMessageTextAndKeyboard(
                        $chatId,
                        $messageId,
                        $originalText,
                        json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => '✅ Approve', 'callback_data' => 'reimburse_approve_' . $id],
                                    ['text' => '❌ Reject', 'callback_data' => 'reimburse_reject_' . $id]
                                ]
                            ]
                        ])
                    );
                    $this->answerCallbackQuery($callbackQueryId, "Silakan pilih ulang status.");
                    return response()->json(['status' => 'success']);
                }

                // Untuk approve / reject
                $superadmin = User::whereIn('jabatan', ['Superadmin', 'Direksi'])->first();

                // Update data reimburse
                $reimburse->status = $action === 'approve' ? 'approved' : 'rejected';
                $reimburse->approved_at = now();
                $reimburse->approved_by = $superadmin ? $superadmin->id : null;
                $reimburse->save();

                // Notifikasi toast (pop up) di Telegram saat diklik
                $statusText = $action === 'approve' ? 'Di-approve' : 'Di-reject';
                $this->answerCallbackQuery($callbackQueryId, "Reimburse $id berhasil $statusText!");

                // Hapus tombol sebelumnya, tampilkan status dan tombol Edit
                $originalText = preg_replace('/\n\n\*\[STATUS: Telah .*? melalui Bot\]\*/', '', $message['text']);
                $newText = "{$originalText}\n\n*[STATUS: Telah $statusText melalui Bot]*";

                $this->editMessageTextAndKeyboard(
                    $chatId, 
                    $messageId, 
                    $newText,
                    json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => '✏️ Edit Status', 'callback_data' => 'reimburse_edit_' . $id]
                            ]
                        ]
                    ])
                );

                return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['status' => 'ignored']);
    }

    private function answerCallbackQuery($callbackQueryId, $text)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        Http::post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => false
        ]);
    }

    private function editMessageTextAndKeyboard($chatId, $messageId, $text, $replyMarkup = null)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
        
        if ($replyMarkup) {
            $payload['reply_markup'] = $replyMarkup;
        }

        Http::post("https://api.telegram.org/bot{$botToken}/editMessageText", $payload);
    }
}
