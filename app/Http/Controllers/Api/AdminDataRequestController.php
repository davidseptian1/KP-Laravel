<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDataRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DataRequest::with('form')->orderByDesc('tanggal_pengajuan');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('username_akun', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => true,
            'message' => 'List monitoring pengajuan data',
            'data' => $query->paginate(15),
        ]);
    }

    public function show(int $id)
    {
        $item = DataRequest::with(['form', 'approver'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Detail pengajuan data',
            'data' => $item,
        ]);
    }

    public function update(Request $request, int $id, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
            'catatan_admin' => 'nullable|string',
        ]);

        $item = DataRequest::findOrFail($id);
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
            'type' => 'data_request_status_updated',
            'reference_id' => $item->id,
            'message' => 'Status pengajuan data diperbarui: ' . $item->kode_pengajuan,
            'is_read' => false,
        ]);

        $targetNumber = $this->normalizePhone($item->wa_pengisi);
        if ($targetNumber) {
            $message = "📢 UPDATE PENGAJUAN DATA\n\n" .
                "Kode        : {$item->kode_pengajuan}\n" .
                "Email Lama  : {$item->email_lama}\n" .
                "Email Baru  : " . ($item->email_baru ?? '-') . "\n" .
                "Jenis       : {$item->jenis_perubahan}\n" .
                "Approved At : " . ($item->approved_at ? Carbon::parse($item->approved_at)->format('d/m/Y H:i') : '-') . "\n" .
                "Status      : {$item->status}\n" .
                "Catatan     : " . ($item->catatan_admin ?? '-') . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($targetNumber, $message);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status pengajuan data berhasil diperbarui',
            'data' => $item,
        ]);
    }

    public function destroy(int $id)
    {
        $item = DataRequest::findOrFail($id);

        foreach ([$item->foto_ktp, $item->foto_selfie] as $path) {
            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan data berhasil dihapus',
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
