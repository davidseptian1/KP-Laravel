<?php

namespace App\Http\Controllers;

use App\Models\NotificationItem;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AdminReimburseWebController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Reimburse::with(['user', 'form'])->orderByDesc('tanggal_pengajuan');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_reimburse', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('admin.reimburse.index', [
            'title' => 'Reimburse',
            'menuAdminReimburse' => 'active',
            'items' => $items,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function update(Request $request, int $id, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
            'catatan_admin' => 'nullable|string',
            'payment_proof_type' => 'nullable|in:text,image',
            'payment_proof_text' => 'nullable|string',
            'payment_proof_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $reimburse = Reimburse::with('user')->findOrFail($id);
        $admin = Auth::user();

        $reimburse->status = $validated['status'];
        $reimburse->catatan_admin = $validated['catatan_admin'] ?? null;

        if (!empty($validated['payment_proof_type'])) {
            $reimburse->payment_proof_type = $validated['payment_proof_type'];

            if ($validated['payment_proof_type'] === 'text') {
                $reimburse->payment_proof_text = $validated['payment_proof_text'] ?? null;
                $reimburse->payment_proof_image = null;
            }

            if ($validated['payment_proof_type'] === 'image' && $request->hasFile('payment_proof_image')) {
                $path = $request->file('payment_proof_image')->store('reimburse/payment-proof', 'local');
                $reimburse->payment_proof_image = $path;
                $reimburse->payment_proof_text = null;
            }
        }

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $reimburse->approved_by = $admin->id;
            $reimburse->approved_at = now();
        } else {
            $reimburse->approved_by = null;
            $reimburse->approved_at = null;
        }

        $reimburse->save();

        NotificationItem::create([
            'type' => 'reimburse_status_updated',
            'reference_id' => $reimburse->id,
            'message' => 'Status reimburse diperbarui: ' . $reimburse->kode_reimburse,
            'is_read' => false,
        ]);

        $targetNumber = $reimburse->wa_pengisi;
        if ($targetNumber) {
            $message = "📢 UPDATE REIMBURSE\n\n" .
                "Kode        : {$reimburse->kode_reimburse}\n" .
                "Nama        : {$reimburse->nama}\n" .
                "Barang      : {$reimburse->nama_barang}\n" .
                "Nominal     : Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n" .
                "Approved At : " . ($reimburse->approved_at ? Carbon::parse($reimburse->approved_at)->format('d/m/Y H:i') : '-') . "\n" .
                "Status      : {$reimburse->status}\n" .
                "Catatan     : " . ($reimburse->catatan_admin ?? '-') . "\n" .
                "Bukti Bayar : " . $this->formatPaymentProof($reimburse) . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($targetNumber, $message);
        }

        return redirect()->route('admin.reimburse.index')->with('success', 'Status reimburse berhasil diperbarui');
    }

    public function download(int $id)
    {
        $reimburse = Reimburse::findOrFail($id);

        $path = $reimburse->bukti_file;
        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.reimburse.index')->with('error', 'File bukti tidak ditemukan');
        }

        return Storage::disk('local')->download($path);
    }

    public function sendWa(int $id, WhatsAppMetricService $whatsApp)
    {
        $reimburse = Reimburse::findOrFail($id);

        $targetNumber = $reimburse->wa_pengisi ?: $reimburse->wa_penerima;

        if (!$targetNumber) {
            return redirect()->route('admin.reimburse.index')->with('error', 'Nomor WA pengisi belum tersedia');
        }

        $message = "📢 UPDATE REIMBURSE\n\n" .
            "Kode        : {$reimburse->kode_reimburse}\n" .
            "Nama        : {$reimburse->nama}\n" .
            "Barang      : {$reimburse->nama_barang}\n" .
            "Nominal     : Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n" .
            "Approved At : " . ($reimburse->approved_at ? Carbon::parse($reimburse->approved_at)->format('d/m/Y H:i') : '-') . "\n" .
            "Status      : {$reimburse->status}\n" .
            "Catatan     : " . ($reimburse->catatan_admin ?? '-') . "\n\n" .
            "Bukti Bayar : " . $this->formatPaymentProof($reimburse) . "\n\n" .
            "Terima kasih.";

        $whatsApp->sendText($targetNumber, $message);

        return redirect()->route('admin.reimburse.index')->with('success', 'Pesan WA terkirim');
    }

    public function view(int $id, int $index = 0)
    {
        $reimburse = Reimburse::findOrFail($id);
        $files = $reimburse->bukti_files ?? [];
        $path = $files[$index] ?? $reimburse->bukti_file;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.reimburse.index')->with('error', 'File bukti tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function viewPaymentProof(int $id)
    {
        $reimburse = Reimburse::findOrFail($id);
        $path = $reimburse->payment_proof_image;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.reimburse.index')->with('error', 'Bukti pembayaran tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    private function formatPaymentProof(Reimburse $reimburse): string
    {
        if ($reimburse->payment_proof_type === 'text' && $reimburse->payment_proof_text) {
            return $reimburse->payment_proof_text;
        }

        if ($reimburse->payment_proof_type === 'image' && $reimburse->payment_proof_image) {
            return URL::signedRoute('public.reimburse.payment-proof', ['id' => $reimburse->id]);
        }

        return '-';
    }
}
