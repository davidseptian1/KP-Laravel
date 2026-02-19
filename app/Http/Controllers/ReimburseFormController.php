<?php

namespace App\Http\Controllers;

use App\Models\ReimburseForm;
use App\Models\Reimburse;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReimburseFormController extends Controller
{
    public function show(string $token)
    {
        $form = ReimburseForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        return view('staff.reimburse.form', [
            'title' => 'Form Reimburse',
            'menuReimburse' => 'active',
            'form' => $form,
        ]);
    }

    public function submit(Request $request, string $token, WhatsAppMetricService $whatsApp)
    {
        $form = ReimburseForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'payment_method' => 'required|in:ewallet,bank',
            'ewallet_provider' => 'required_if:payment_method,ewallet|nullable|string|max:50',
            'ewallet_number' => 'required_if:payment_method,ewallet|nullable|string|max:50',
            'ewallet_name' => 'required_if:payment_method,ewallet|nullable|string|max:100',
            'bank_provider' => 'required_if:payment_method,bank|nullable|string|max:50',
            'bank_account_number' => 'required_if:payment_method,bank|nullable|string|max:50',
            'bank_account_name' => 'required_if:payment_method,bank|nullable|string|max:100',
            'divisi' => 'required|in:accounting,act,server,hrd,direksi,gudang,sosmed,host live,it',
            'nominal' => 'required|numeric|min:0',
            'nama_barang' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'bukti' => 'required',
            'bukti.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'wa_pengisi' => 'required|string|max:20',
        ]);

        $noRekening = $validated['payment_method'] === 'ewallet'
            ? 'E-Wallet - ' . ($validated['ewallet_provider'] ?? '-') . ' - ' . ($validated['ewallet_number'] ?? '-') . ' - ' . ($validated['ewallet_name'] ?? '-')
            : 'Bank - ' . ($validated['bank_provider'] ?? '-') . ' - ' . ($validated['bank_account_number'] ?? '-') . ' - ' . ($validated['bank_account_name'] ?? '-');

        $reimburse = DB::transaction(function () use ($validated, $request, $form, $noRekening) {
            $buktiFiles = [];
            foreach ($request->file('bukti', []) as $file) {
                $buktiFiles[] = $file->store('reimburse', 'local');
            }

            $kode = $this->generateKodeReimburse();

            $adminNumbers = config('whatsapp.admin_numbers', []);
            $waPenerima = $adminNumbers[0] ?? null;

            $reimburse = Reimburse::create([
                'user_id' => null,
                'form_id' => $form->id,
                'nama' => $validated['nama'],
                'no_rekening' => $noRekening,
                'payment_method' => $validated['payment_method'],
                'payment_provider' => $validated['payment_method'] === 'ewallet'
                    ? ($validated['ewallet_provider'] ?? null)
                    : ($validated['bank_provider'] ?? null),
                'payment_account_number' => $validated['payment_method'] === 'ewallet'
                    ? ($validated['ewallet_number'] ?? null)
                    : ($validated['bank_account_number'] ?? null),
                'payment_account_name' => $validated['payment_method'] === 'ewallet'
                    ? ($validated['ewallet_name'] ?? null)
                    : ($validated['bank_account_name'] ?? null),
                'divisi' => $validated['divisi'],
                'kode_reimburse' => $kode,
                'tanggal_pengajuan' => now(),
                'nominal' => $validated['nominal'],
                'nama_barang' => $validated['nama_barang'],
                'keperluan' => $validated['keperluan'],
                'keterangan' => $validated['keperluan'],
                'bukti_file' => $buktiFiles[0] ?? null,
                'bukti_files' => $buktiFiles,
                'wa_penerima' => $waPenerima,
                'wa_pengisi' => $validated['wa_pengisi'],
                'status' => 'pending',
            ]);

            NotificationItem::create([
                'type' => 'reimburse_submitted',
                'reference_id' => $reimburse->id,
                'message' => 'Reimburse baru masuk: ' . $kode,
                'is_read' => false,
            ]);

            return $reimburse;
        });

        $monitoringUrl = route('admin.reimburse.index');
        $message = "📌 REIMBURSE BARU MASUK\n\n" .
            "Kode        : {$reimburse->kode_reimburse}\n" .
            "Nama        : {$reimburse->nama}\n" .
            "No Rekening : {$reimburse->no_rekening}\n" .
            "Divisi      : {$reimburse->divisi}\n" .
            "Nominal     : Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n" .
            "WA Pengisi  : " . ($reimburse->wa_pengisi ?? '-') . "\n" .
            "Keperluan   : {$reimburse->keperluan}\n" .
            "Tanggal     : " . Carbon::parse($reimburse->tanggal_pengajuan)->format('d/m/Y H:i') . "\n\n" .
            "Monitoring  : {$monitoringUrl}";

        $whatsApp->sendToAdmins($message);
        if ($reimburse->wa_penerima) {
            $whatsApp->sendText($reimburse->wa_penerima, $message);
        }

        return redirect()->back()->with([
            'success' => 'Pengajuan reimburse berhasil dikirim',
            'reimburse_submitted' => true,
        ]);
    }

    private function generateKodeReimburse(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "RB-{$datePart}-";

        $last = Reimburse::where('kode_reimburse', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) Str::after($last->kode_reimburse, $prefix);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function buildWaLink(string $number, string $message): string
    {
        $digits = preg_replace('/\D+/', '', $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
    }

}
