<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationItem;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminReimburseController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Reimburse::query()
            ->with('user')
            ->orderByDesc('tanggal_pengajuan');

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

        $items = $query->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'List reimburse admin',
            'data' => $items,
        ]);
    }

    public function show(int $id)
    {
        $reimburse = Reimburse::with(['user', 'approver'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Detail reimburse',
            'data' => $reimburse,
        ]);
    }

    public function update(Request $request, int $id, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision',
            'catatan_admin' => 'nullable|string',
        ]);

        $reimburse = Reimburse::with('user')->findOrFail($id);
        $admin = $request->user();

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

        return response()->json([
            'status' => true,
            'message' => 'Status reimburse berhasil diperbarui',
            'data' => [
                'kode_reimburse' => $reimburse->kode_reimburse,
                'status' => $reimburse->status,
                'approved_at' => $reimburse->approved_at
                    ? Carbon::parse($reimburse->approved_at)->toDateTimeString()
                    : null,
            ],
        ]);
    }

    public function download(int $id)
    {
        $reimburse = Reimburse::findOrFail($id);

        if (!Storage::disk('local')->exists($reimburse->bukti_file)) {
            return response()->json([
                'status' => false,
                'message' => 'File bukti tidak ditemukan',
            ], 404);
        }

        return Storage::disk('local')->download($reimburse->bukti_file);
    }
}
