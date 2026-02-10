<?php

namespace App\Http\Controllers\DataRequest;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\NotificationItem;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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

        $items = $query->paginate(15)->withQueryString();

        return view('admin.data-request.index', [
            'title' => 'Pengajuan Data',
            'menuAdminDataRequest' => 'active',
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

        $dataRequest = DataRequest::findOrFail($id);

        $dataRequest->status = $validated['status'];
        $dataRequest->catatan_admin = $validated['catatan_admin'] ?? null;

        if (in_array($validated['status'], ['approved', 'rejected'], true)) {
            $dataRequest->approved_by = $request->user()->id;
            $dataRequest->approved_at = now();
        } else {
            $dataRequest->approved_by = null;
            $dataRequest->approved_at = null;
        }

        $dataRequest->save();

        NotificationItem::create([
            'type' => 'data_request_status_updated',
            'reference_id' => $dataRequest->id,
            'message' => 'Status pengajuan data diperbarui: ' . $dataRequest->kode_pengajuan,
            'is_read' => false,
        ]);

        if ($dataRequest->wa_pengisi) {
            $message = "📢 UPDATE PENGAJUAN DATA\n\n" .
                "Kode        : {$dataRequest->kode_pengajuan}\n" .
                "Status      : {$dataRequest->status}\n" .
                "Catatan     : " . ($dataRequest->catatan_admin ?? '-') . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($dataRequest->wa_pengisi, $message);
        }

        return redirect()->route('admin.data-request.index')->with('success', 'Status pengajuan data berhasil diperbarui');
    }

    public function viewFile(int $id, string $type)
    {
        $dataRequest = DataRequest::findOrFail($id);
        $path = $type === 'ktp' ? $dataRequest->foto_ktp : $dataRequest->foto_selfie;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.data-request.index')->with('error', 'File bukti tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function downloadFile(int $id, string $type)
    {
        $dataRequest = DataRequest::findOrFail($id);
        $path = $type === 'ktp' ? $dataRequest->foto_ktp : $dataRequest->foto_selfie;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.data-request.index')->with('error', 'File bukti tidak ditemukan');
        }

        return Storage::disk('local')->download($path);
    }

    public function sendWa(int $id, WhatsAppMetricService $whatsApp)
    {
        $dataRequest = DataRequest::findOrFail($id);

        if (!$dataRequest->wa_pengisi) {
            return redirect()->route('admin.data-request.index')->with('error', 'Nomor WA pengisi belum tersedia');
        }

        $message = "📢 UPDATE PENGAJUAN DATA\n\n" .
            "Kode        : {$dataRequest->kode_pengajuan}\n" .
            "Status      : {$dataRequest->status}\n" .
            "Catatan     : " . ($dataRequest->catatan_admin ?? '-') . "\n\n" .
            "Terima kasih.";

        $whatsApp->sendText($dataRequest->wa_pengisi, $message);

        return redirect()->route('admin.data-request.index')->with('success', 'Pesan WA terkirim');
    }
}
