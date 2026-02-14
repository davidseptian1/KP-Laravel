<?php

namespace App\Http\Controllers\LoanRequest;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;

class AdminLoanRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = LoanRequest::with('form')->orderByDesc('tanggal_pengajuan');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nama_server', 'like', "%{$search}%")
                    ->orWhere('barang_dipinjam', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('admin.loan-request.index', [
            'title' => 'Peminjaman Barang',
            'menuAdminLoanRequest' => 'active',
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
        ]);

        $loanRequest = LoanRequest::findOrFail($id);

        $loanRequest->status = $validated['status'];
        $loanRequest->catatan_admin = $validated['catatan_admin'] ?? null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $loanRequest->approved_by = $request->user()->id;
            $loanRequest->approved_at = now();
        } else {
            $loanRequest->approved_by = null;
            $loanRequest->approved_at = null;
        }

        $loanRequest->save();

        NotificationItem::create([
            'type' => 'loan_request_status_updated',
            'reference_id' => $loanRequest->id,
            'message' => 'Status peminjaman barang diperbarui: ' . $loanRequest->kode_pengajuan,
            'is_read' => false,
        ]);

        $targetNumber = $this->normalizePhone($loanRequest->wa_pengisi);
        if ($targetNumber) {
            $message = "📢 UPDATE PEMINJAMAN BARANG\n\n" .
                "Kode        : {$loanRequest->kode_pengajuan}\n" .
                "Status      : {$loanRequest->status}\n" .
                "Catatan     : " . ($loanRequest->catatan_admin ?? '-') . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($targetNumber, $message);
        }

        return redirect()->route('admin.loan-request.index')->with('success', 'Status peminjaman barang berhasil diperbarui');
    }

    public function sendWa(int $id, WhatsAppMetricService $whatsApp)
    {
        $loanRequest = LoanRequest::findOrFail($id);

        $targetNumber = $this->normalizePhone($loanRequest->wa_pengisi);
        if (!$targetNumber) {
            return redirect()->route('admin.loan-request.index')->with('error', 'Nomor WA pengisi belum tersedia');
        }

        $message = "📢 UPDATE PEMINJAMAN BARANG\n\n" .
            "Kode        : {$loanRequest->kode_pengajuan}\n" .
            "Status      : {$loanRequest->status}\n" .
            "Catatan     : " . ($loanRequest->catatan_admin ?? '-') . "\n\n" .
            "Terima kasih.";

        $whatsApp->sendText($targetNumber, $message);

        return redirect()->route('admin.loan-request.index')->with('success', 'Pesan WA terkirim');
    }

    private function normalizePhone(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        return $digits;
    }

}
