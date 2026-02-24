<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;

class AdminDepositController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'search' => 'nullable|string|max:100',
            'server' => 'nullable|string|max:100',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $status = $validated['status'] ?? null;
        $search = $validated['search'] ?? null;
        $server = $validated['server'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $query = Deposit::query()->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'like', "%{$search}%")
                    ->orWhere('server', 'like', "%{$search}%")
                    ->orWhere('no_rek', 'like', "%{$search}%");
            });
        }

        if ($server) {
            $query->where('server', $server);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return response()->json([
            'status' => true,
            'message' => 'List monitoring deposit',
            'data' => $query->paginate(15),
        ]);
    }

    public function show(int $id)
    {
        $item = Deposit::findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Detail deposit',
            'data' => $item,
        ]);
    }

    public function updateDetails(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'reply_penambahan' => 'required|string',
            'jam' => 'required|date_format:H:i',
        ]);

        $item = Deposit::findOrFail($id);
        $item->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data request deposit berhasil diedit',
            'data' => $item,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,selesai',
        ]);

        $item = Deposit::findOrFail($id);
        $item->status = $validated['status'];
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Status request deposit berhasil diperbarui',
            'data' => $item,
        ]);
    }

    public function destroy(int $id)
    {
        $item = Deposit::findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Request deposit berhasil dihapus',
        ]);
    }
}
