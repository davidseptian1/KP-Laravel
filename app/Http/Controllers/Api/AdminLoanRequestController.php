<?php

namespace App\Http\Controllers\Api;

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

        return response()->json([
            'status' => true,
            'message' => 'List monitoring peminjaman barang',
            'data' => $query->paginate(15),
        ]);
    }

    public function show(int $id)
    {
        $item = LoanRequest::with(['form', 'approver'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Detail peminjaman barang',
            'data' => $item,
        ]);
    }

    public function update(Request $request, int $id, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
            'catatan_admin' => 'nullable|string',
        ]);

        $item = LoanRequest::findOrFail($id);
        $item->status = $validated['status'];
        $item->catatan_admin = $validated['catatan_admin'] ?? null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $item->approved_by = $request->user()->id;
            $item->approved_at = now();
        } else {
            $item->approved_by = null;
            $item->approved_at = null;
        }

        $item->save();

        NotificationItem::create([
            'type' => 'loan_request_status_updated',
            'reference_id' => $item->id,
            'message' => 'Status peminjaman barang diperbarui: ' . $item->kode_pengajuan,
            'is_read' => false,
        ]);

        $targetNumber = $this->normalizePhone($item->wa_pengisi);
        if ($targetNumber) {
            $message = "📢 UPDATE PEMINJAMAN BARANG\n\n" .
                "Kode        : {$item->kode_pengajuan}\n" .
                "Status      : {$item->status}\n" .
                "Catatan     : " . ($item->catatan_admin ?? '-') . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($targetNumber, $message);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status peminjaman barang berhasil diperbarui',
            'data' => $item,
        ]);
    }

    public function destroy(int $id)
    {
        $item = LoanRequest::findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data peminjaman barang berhasil dihapus',
        ]);
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
