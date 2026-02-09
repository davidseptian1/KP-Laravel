<?php

namespace App\Http\Controllers;

use App\Models\NotificationItem;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        ]);

        $reimburse = Reimburse::with('user')->findOrFail($id);
        $admin = Auth::user();

        $reimburse->status = $validated['status'];
        $reimburse->catatan_admin = $validated['catatan_admin'] ?? null;

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

        $user = $reimburse->user;
        if ($user && $user->no_hp) {
            $message = "📢 UPDATE REIMBURSE\n\n" .
                "Kode        : {$reimburse->kode_reimburse}\n" .
                "Status      : {$reimburse->status}\n" .
                "Catatan     : " . ($reimburse->catatan_admin ?? '-') . "\n\n" .
                "Terima kasih.";

            $whatsApp->sendText($user->no_hp, $message);
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

        if (!$reimburse->wa_penerima) {
            return redirect()->route('admin.reimburse.index')->with('error', 'Nomor WA penerima belum tersedia');
        }

        $message = "📢 UPDATE REIMBURSE\n\n" .
            "Kode        : {$reimburse->kode_reimburse}\n" .
            "Status      : {$reimburse->status}\n" .
            "Catatan     : " . ($reimburse->catatan_admin ?? '-') . "\n\n" .
            "Terima kasih.";

        $whatsApp->sendText($reimburse->wa_penerima, $message);

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
}
