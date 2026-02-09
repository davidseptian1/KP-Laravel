<?php

namespace App\Http\Controllers;

use App\Models\NotificationItem;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReimburseWebController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $items = Reimburse::where('user_id', $user->id)
            ->orderByDesc('tanggal_pengajuan')
            ->paginate(10);

        return view('staff.reimburse.index', [
            'title' => 'Reimburse',
            'menuReimburse' => 'active',
            'items' => $items,
        ]);
    }

    public function store(Request $request, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'divisi' => 'required|in:accounting,act,server,hrd,direksi,gudang,sosmed,host live,it',
            'nominal' => 'required|numeric|min:0',
            'nama_barang' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'bukti' => 'required',
            'bukti.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'wa_pengisi' => 'required|string|max:20',
            'form_id' => 'nullable|exists:reimburse_forms,id',
        ]);

        $user = Auth::user();

        $reimburse = DB::transaction(function () use ($validated, $request, $user) {
            $buktiFiles = [];
            foreach ($request->file('bukti', []) as $file) {
                $buktiFiles[] = $file->store('reimburse', 'local');
            }

            $kode = $this->generateKodeReimburse();

            $adminNumbers = config('whatsapp.admin_numbers', []);
            $waPenerima = $adminNumbers[0] ?? null;

            $reimburse = Reimburse::create([
                'user_id' => $user->id,
                'form_id' => $validated['form_id'] ?? null,
                'nama' => $validated['nama'],
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

        if ($reimburse->wa_penerima) {
            $waLink = $this->buildWaLink($reimburse->wa_penerima, $message);
            return redirect()->away($waLink);
        }

        return redirect()->route('reimburse.index')->with('success', 'Pengajuan reimburse berhasil dikirim');
    }

    private function buildWaLink(string $number, string $message): string
    {
        $digits = preg_replace('/\D+/', '', $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
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
}
