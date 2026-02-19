<?php

namespace App\Http\Controllers\DataRequest;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\DataRequestForm;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DataRequestFormController extends Controller
{
    public function show(string $token)
    {
        $form = DataRequestForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        return view('staff.data-request.form', [
            'title' => 'Form Pengajuan Data',
            'menuDataRequest' => 'active',
            'form' => $form,
        ]);
    }

    public function submit(Request $request, string $token, WhatsAppMetricService $whatsApp)
    {
        $form = DataRequestForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        $validated = $request->validate([
            'aplikasi' => 'required|in:belanja kuota,238,crm payment,aira',
            'username_akun' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
            'email_lama' => 'required|email',
            'email_baru' => 'nullable|email',
            'nama_pemohon' => 'required|string|max:255',
            'riwayat_transaksi' => 'required|string',
            'saldo_terakhir' => 'required|numeric|min:0',
            'jenis_perubahan' => 'required|in:perubahan email,nomor hp,password',
            'alasan_perubahan' => 'required|string',
            'foto_ktp' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'foto_selfie' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'wa_pengisi' => 'required|string|max:20',
        ]);

        $validated['nomor_hp'] = $this->normalizePhone($validated['nomor_hp']);
        $validated['wa_pengisi'] = $this->normalizePhone($validated['wa_pengisi']);

        $dataRequest = DB::transaction(function () use ($validated, $request, $form) {
            $fotoKtp = $request->file('foto_ktp')->store('data-request', 'local');
            $fotoSelfie = $request->file('foto_selfie')->store('data-request', 'local');

            $kode = $this->generateKodePengajuan();
            $adminNumbers = config('whatsapp.admin_numbers', []);
            $waPenerima = $adminNumbers[0] ?? null;

            $dataRequest = DataRequest::create([
                'user_id' => null,
                'form_id' => $form->id,
                'kode_pengajuan' => $kode,
                'tanggal_pengajuan' => now(),
                'aplikasi' => $validated['aplikasi'],
                'username_akun' => $validated['username_akun'],
                'nomor_hp' => $validated['nomor_hp'],
                'email_lama' => $validated['email_lama'],
                'email_baru' => $validated['email_baru'] ?? null,
                'nama_pemohon' => $validated['nama_pemohon'],
                'riwayat_transaksi' => $validated['riwayat_transaksi'],
                'saldo_terakhir' => $validated['saldo_terakhir'],
                'jenis_perubahan' => $validated['jenis_perubahan'],
                'alasan_perubahan' => $validated['alasan_perubahan'],
                'foto_ktp' => $fotoKtp,
                'foto_selfie' => $fotoSelfie,
                'wa_penerima' => $waPenerima,
                'wa_pengisi' => $validated['wa_pengisi'],
                'status' => 'pending',
            ]);

            NotificationItem::create([
                'type' => 'data_request_submitted',
                'reference_id' => $dataRequest->id,
                'message' => 'Pengajuan data baru: ' . $kode,
                'is_read' => false,
            ]);

            return $dataRequest;
        });

        $monitoringUrl = route('admin.data-request.index');
        $message = "🧾 *PENGAJUAN DATA BARU*\n\n" .
            "Kode        : {$dataRequest->kode_pengajuan}\n" .
            "Aplikasi    : {$dataRequest->aplikasi}\n" .
            "Username    : {$dataRequest->username_akun}\n" .
            "Nomor HP    : {$dataRequest->nomor_hp}\n" .
            "Email Lama  : {$dataRequest->email_lama}\n" .
            "Email Baru  : " . ($dataRequest->email_baru ?? '-') . "\n" .
            "Pemohon     : {$dataRequest->nama_pemohon}\n" .
            "Jenis       : {$dataRequest->jenis_perubahan}\n" .
            "Alasan      : {$dataRequest->alasan_perubahan}\n" .
            "WA Pengisi  : {$dataRequest->wa_pengisi}\n" .
            "Tanggal     : " . Carbon::parse($dataRequest->tanggal_pengajuan)->format('d/m/Y H:i') . "\n\n" .
            "Monitoring  : {$monitoringUrl}";

        $whatsApp->sendToAdmins($message);
        if ($dataRequest->wa_penerima) {
            $whatsApp->sendText($dataRequest->wa_penerima, $message);
        }

        $adminNumber = config('whatsapp.admin_numbers.0') ?: '-';
        $replyText = "FORM PENGAJUAN DATA\n" .
            "Kode         : {$dataRequest->kode_pengajuan}\n" .
            "Aplikasi     : " . strtoupper($dataRequest->aplikasi) . "\n" .
            "Username     : {$dataRequest->username_akun}\n" .
            "Nomor HP     : {$dataRequest->nomor_hp}\n" .
            "Email Lama   : {$dataRequest->email_lama}\n" .
            "Email Baru   : " . ($dataRequest->email_baru ?: '-') . "\n" .
            "Nama Pemohon : {$dataRequest->nama_pemohon}\n" .
            "Jenis        : {$dataRequest->jenis_perubahan}\n" .
            "Alasan       : {$dataRequest->alasan_perubahan}\n" .
            "WA Pengisi   : {$dataRequest->wa_pengisi}\n" .
            "KTP          : Terlampir\n" .
            "Selfie KTP   : Terlampir\n\n" .
            "Note: kirimkan ke WhatsApp admin, jika ingin diproses {$adminNumber}";

        return redirect()->back()->with([
            'success' => 'Pengajuan data berhasil dikirim',
            'data_request_submitted' => true,
            'data_request_reply_text' => $replyText,
        ]);
    }

    private function generateKodePengajuan(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "PD-{$datePart}-";

        $last = DataRequest::where('kode_pengajuan', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) Str::after($last->kode_pengajuan, $prefix);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function buildWaLink(?string $number, string $message): string
    {
        if (!$number) {
            return route('admin.data-request.index');
        }
        $digits = $this->normalizePhone($number);
        return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
    }

    private function normalizePhone(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return $digits;
    }
}
