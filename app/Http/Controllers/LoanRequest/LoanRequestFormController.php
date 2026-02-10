<?php

namespace App\Http\Controllers\LoanRequest;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\LoanRequestForm;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoanRequestFormController extends Controller
{
    public function show(string $token)
    {
        $form = LoanRequestForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        return view('staff.loan-request.form', [
            'title' => 'Form Peminjaman Barang',
            'menuLoanRequest' => 'active',
            'form' => $form,
        ]);
    }

    public function submit(Request $request, string $token, WhatsAppMetricService $whatsApp)
    {
        $form = LoanRequestForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        $validated = $request->validate([
            'nama_server' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'nomor_hp' => 'required|string|max:20',
            'barang_dipinjam' => 'required|string|max:255',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'nullable|date',
            'wa_pengisi' => 'required|string|max:20',
        ]);

        $loanRequest = DB::transaction(function () use ($validated, $form) {
            $kode = $this->generateKodePengajuan();
            $adminNumbers = config('whatsapp.admin_numbers', []);
            $waPenerima = $adminNumbers[0] ?? null;

            $loanRequest = LoanRequest::create([
                'user_id' => null,
                'form_id' => $form->id,
                'kode_pengajuan' => $kode,
                'tanggal_pengajuan' => now(),
                'nama_server' => $validated['nama_server'],
                'nomor_hp' => $validated['nomor_hp'],
                'keperluan' => $validated['keperluan'],
                'barang_dipinjam' => $validated['barang_dipinjam'],
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_kembali' => $validated['tanggal_kembali'] ?? null,
                'wa_penerima' => $waPenerima,
                'wa_pengisi' => $validated['wa_pengisi'],
                'status' => 'pending',
            ]);

            NotificationItem::create([
                'type' => 'loan_request_submitted',
                'reference_id' => $loanRequest->id,
                'message' => 'Peminjaman barang baru: ' . $kode,
                'is_read' => false,
            ]);

            return $loanRequest;
        });

        $monitoringUrl = route('admin.loan-request.index');
        $message = "📦 *PEMINJAMAN BARANG BARU*\n\n" .
            "Kode        : {$loanRequest->kode_pengajuan}\n" .
            "Nama Server : {$loanRequest->nama_server}\n" .
            "Nomor HP    : {$loanRequest->nomor_hp}\n" .
            "Keperluan   : {$loanRequest->keperluan}\n" .
            "Barang      : {$loanRequest->barang_dipinjam}\n" .
            "Tgl Pinjam  : " . Carbon::parse($loanRequest->tanggal_pinjam)->format('d/m/Y H:i') . "\n" .
            "Tgl Balik   : " . ($loanRequest->tanggal_kembali ? Carbon::parse($loanRequest->tanggal_kembali)->format('d/m/Y H:i') : '-') . "\n" .
            "WA Pengisi  : {$loanRequest->wa_pengisi}\n" .
            "Tanggal     : " . Carbon::parse($loanRequest->tanggal_pengajuan)->format('d/m/Y H:i') . "\n\n" .
            "Monitoring  : {$monitoringUrl}";

        $whatsApp->sendToAdmins($message);
        if ($loanRequest->wa_penerima) {
            $whatsApp->sendText($loanRequest->wa_penerima, $message);
        }

        $waLink = $this->buildWaLink($loanRequest->wa_penerima, $message);
        return redirect()->away($waLink);
    }

    private function generateKodePengajuan(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "PJ-{$datePart}-";

        $last = LoanRequest::where('kode_pengajuan', 'like', $prefix . '%')
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
            return route('admin.loan-request.index');
        }
        $digits = preg_replace('/\D+/', '', $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
    }
}
