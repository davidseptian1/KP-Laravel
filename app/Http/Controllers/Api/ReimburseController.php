<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationItem;
use App\Models\Reimburse;
use App\Services\WhatsAppMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReimburseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = Reimburse::query()
            ->where('user_id', $user->id)
            ->orderByDesc('tanggal_pengajuan');

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'List reimburse user',
            'data' => $items,
        ]);
    }

    public function store(Request $request, WhatsAppMetricService $whatsApp)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:50',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();

        $reimburse = DB::transaction(function () use ($validated, $request, $user) {
            $buktiPath = $request->file('bukti')->store('reimburse', 'local');

            $kode = $this->generateKodeReimburse();

            $reimburse = Reimburse::create([
                'user_id' => $user->id,
                'kode_reimburse' => $kode,
                'tanggal_pengajuan' => now(),
                'kategori' => $validated['kategori'],
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'],
                'bukti_file' => $buktiPath,
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

        $message = "📌 REIMBURSE BARU MASUK\n\n" .
            "Kode        : {$reimburse->kode_reimburse}\n" .
            "Nama User   : {$user->nama}\n" .
            "Kategori    : {$reimburse->kategori}\n" .
            "Nominal     : Rp " . number_format($reimburse->nominal, 0, ',', '.') . "\n" .
            "Tanggal     : " . Carbon::parse($reimburse->tanggal_pengajuan)->format('d/m/Y H:i');

        $whatsApp->sendToAdmins($message);

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan reimburse berhasil dikirim',
            'data' => [
                'kode_reimburse' => $reimburse->kode_reimburse,
                'status' => $reimburse->status,
            ],
        ]);
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
