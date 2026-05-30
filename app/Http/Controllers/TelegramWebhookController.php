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
            if ((string)$chatId !== (string)$allowedChatId) {
                return response()->json(['status' => 'unauthorized chat']);
            }

            // Pola: reimburse_approve_{id} atau reimburse_reject_{id}
            if (preg_match('/^reimburse_(approve|reject)_(\d+)$/', $data, $matches)) {
                $action = $matches[1]; // 'approve' atau 'reject'
                $id = $matches[2];

                $reimburse = Reimburse::find($id);

                if (!$reimburse) {
                    $this->answerCallbackQuery($callbackQueryId, 'Data reimburse tidak ditemukan.');
                    return response()->json(['status' => 'not found']);
                }

                if ($reimburse->status !== 'waiting_approval_direksi') {
                    $this->answerCallbackQuery($callbackQueryId, 'Data ini sudah diproses sebelumnya.');
                    return response()->json(['status' => 'already processed']);
                }

                // Kita asumsikan Direksi pertama yang akan menjadi 'approved_by' jika dibutuhkan,
                // Namun karena ini otomatis, kita bisa gunakan null atau mencari user direksi.
                // Untuk kesederhanaan, mari kita cari user superadmin pertama.
                $superadmin = User::whereIn('jabatan', ['Superadmin', 'Direksi'])->first();

                // Update data reimburse
                $reimburse->status = $action === 'approve' ? 'approved' : 'rejected';
                $reimburse->approved_at = now();
                $reimburse->approved_by = $superadmin ? $superadmin->id : null;
                $reimburse->save();

                // Notifikasi toast (pop up) di Telegram saat diklik
                $statusText = $action === 'approve' ? 'Di-approve' : 'Di-reject';
                $this->answerCallbackQuery($callbackQueryId, "Reimburse $id berhasil $statusText!");

                // Hapus tombol dan perbarui pesan agar tidak diklik dua kali
                $this->editMessageText(
                    $chatId, 
                    $messageId, 
                    "{$message['text']}\n\n*[STATUS: Telah $statusText melalui Bot]*"
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

    private function editMessageText($chatId, $messageId, $text)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        Http::post("https://api.telegram.org/bot{$botToken}/editMessageText", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
