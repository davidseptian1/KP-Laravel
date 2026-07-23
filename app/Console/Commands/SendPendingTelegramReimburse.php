<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reimburse;
use Illuminate\Support\Facades\Http;

class SendPendingTelegramReimburse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimburse:send-pending-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim semua reimburse berstatus Waiting Approval Direksi yang belum terkirim ke Telegram';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            $this->error('TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum di-set di .env');
            return Command::FAILURE;
        }

        $items = Reimburse::where('status', 'waiting_approval_direksi')->get();

        if ($items->isEmpty()) {
            $this->info('Tidak ada reimburse berstatus Waiting Approval Direksi.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($items as $reimburse) {
            $metode = $reimburse->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank';
            $provider = strtoupper((string) $reimburse->payment_provider);
            $noRekeningLengkap = $reimburse->no_rekening ?: "{$metode} - {$provider} - {$reimburse->payment_account_number} - {$reimburse->payment_account_name}";
            $buktiLink = route('admin.reimburse.view', [$reimburse->id, 0]);
            $tanggal = $reimburse->tanggal_pengajuan ? $reimburse->tanggal_pengajuan->format('d M Y H:i') : '-';

            $nama = htmlspecialchars((string)$reimburse->nama);
            $namaBarang = htmlspecialchars((string)$reimburse->nama_barang);
            $keperluan = htmlspecialchars((string)($reimburse->keterangan ?? $reimburse->keperluan ?? '-'));
            $kode = htmlspecialchars((string)$reimburse->kode_reimburse);
            $divisi = htmlspecialchars((string)$reimburse->divisi);
            $noRek = htmlspecialchars((string)$noRekeningLengkap);

            $message = "⏳ <b>Menunggu Approval Direksi</b>\n\n"
                . "<b>FORM REIMBURSE</b>\n"
                . "<b>Kode</b>          : {$kode}\n"
                . "<b>Nama</b>          : {$nama}\n"
                . "<b>Metode</b>        : {$metode}\n"
                . "<b>Provider</b>      : {$provider}\n"
                . "<b>No Rekening</b>   : {$noRek}\n"
                . "<b>Divisi</b>        : {$divisi}\n"
                . "<b>Nominal</b>       : Rp " . number_format((float)$reimburse->nominal, 0, ',', '.') . "\n"
                . "<b>Nama Barang</b>   : {$namaBarang}\n"
                . "<b>Keperluan</b>     : {$keperluan}\n"
                . "<b>WA Pengisi</b>    : {$reimburse->wa_pengisi}\n"
                . "<b>Bukti</b>         : <a href=\"{$buktiLink}\">Lihat Bukti</a>\n"
                . "<b>Tanggal pengajuan</b> : {$tanggal}\n\n"
                . "Mohon Direksi untuk melakukan approval.";

            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => '✅ Approve', 'callback_data' => 'reimburse_approve_' . $reimburse->id],
                            ['text' => '❌ Reject', 'callback_data' => 'reimburse_reject_' . $reimburse->id]
                        ]
                    ]
                ])
            ];

            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
            $count++;
            usleep(300000); // 0.3s delay per message
        }

        $this->info("Berhasil mengirim {$count} data reimburse ke Telegram.");
        return Command::SUCCESS;
    }
}
