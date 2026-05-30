<?php

namespace App\Http\Controllers;

use App\Models\NotificationItem;
use App\Models\DeletionLog;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Exports\ReimburseExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AdminReimburseWebController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

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

        if ($startDate) {
            $query->whereDate('tanggal_pengajuan', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_pengajuan', '<=', $endDate);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('admin.reimburse.index', [
            'title' => 'Reimburse',
            'menuAdminReimburse' => 'active',
            'items' => $items,
            'status' => $status,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function update(Request $request, int $id, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,waiting_approval_direksi,approved,rejected,revision',
            'catatan_admin' => 'nullable|string',
            'payment_proof_type' => 'nullable|in:text,image',
            'payment_proof_text' => 'nullable|string',
            'payment_proof_image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $reimburse = Reimburse::with('user')->findOrFail($id);
        $admin = Auth::user();
        
        $oldStatus = $reimburse->status;

        // Prevent admin from changing status if it's already approved/waiting and they are not superadmin
        // But let's handle this in the view and validate it. Actually, we just update what is sent.
        
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

        if (in_array($validated['status'], ['approved', 'rejected', 'waiting_approval_direksi'], true)) {
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

        // Send to Telegram if needed
        $this->sendTelegramNotification($reimburse, $oldStatus, $validated);

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

    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'delete_reason' => 'required|string|max:1000',
        ]);

        $reimburse = Reimburse::findOrFail($id);

        $user = $request->user();
        DeletionLog::create([
            'module' => 'monitoring_reimburse',
            'reference_id' => $reimburse->id,
            'item_code' => (string) ($reimburse->kode_reimburse ?? $reimburse->id),
            'reason' => trim((string) $validated['delete_reason']),
            'deleted_by_id' => $user?->id,
            'deleted_by_name' => $user?->nama,
            'deleted_by_role' => $user?->jabatan,
            'snapshot' => [
                'kode_reimburse' => $reimburse->kode_reimburse,
                'nama' => $reimburse->nama,
                'nominal' => $reimburse->nominal,
                'status' => $reimburse->status,
            ],
            'deleted_at' => now(),
        ]);

        $paths = [];
        if ($reimburse->bukti_file) {
            $paths[] = $reimburse->bukti_file;
        }
        if (is_array($reimburse->bukti_files)) {
            $paths = array_merge($paths, $reimburse->bukti_files);
        }
        if ($reimburse->payment_proof_image) {
            $paths[] = $reimburse->payment_proof_image;
        }

        foreach (array_unique(array_filter($paths)) as $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        $reimburse->delete();

        return redirect()->route('admin.reimburse.index')->with('success', 'Data reimburse berhasil dihapus');
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

    public function exportExcel(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

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

        if ($startDate) {
            $query->whereDate('tanggal_pengajuan', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal_pengajuan', '<=', $endDate);
        }

        $items = $query->get();

        $fileName = 'reimburse_export_' . now()->format('YmdHis') . '.xlsx';
        return Excel::download(new ReimburseExport($items), $fileName);
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

    private function sendTelegramNotification($reimburse, $oldStatus, $validated)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            return;
        }

        $message = null;

        // Condition 1: Admin approved (waiting_approval_direksi)
        if ($reimburse->status === 'waiting_approval_direksi' && $oldStatus !== 'waiting_approval_direksi') {
            $message = "⏳ *Menunggu Approval Direksi*\n\n"
                . "Kode: {$reimburse->kode_reimburse}\n"
                . "Nama: {$reimburse->nama}\n"
                . "Nominal: Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n"
                . "Keperluan: {$reimburse->nama_barang}\n"
                . "Mohon Direksi untuk melakukan approval.";
        }

        // Condition 2: Admin edits/saves transfer proof and it's already approved
        // The condition for saving transfer proof can be checking if payment proof type is not empty in validated
        if ($reimburse->status === 'approved' && !empty($validated['payment_proof_type'])) {
            $message = "✅ *Data Selesai Ditransfer*\n\n"
                . "Kode: {$reimburse->kode_reimburse}\n"
                . "Nama: {$reimburse->nama}\n"
                . "Nominal: Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n"
                . "Status: Sudah Ditransfer";
        }

        if ($message) {
            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ];
            
            if ($reimburse->status === 'waiting_approval_direksi' && $oldStatus !== 'waiting_approval_direksi') {
                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => '✅ Approve', 'callback_data' => 'reimburse_approve_' . $reimburse->id],
                            ['text' => '❌ Reject', 'callback_data' => 'reimburse_reject_' . $reimburse->id]
                        ]
                    ]
                ]);
            }

            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
        }
    }
}
