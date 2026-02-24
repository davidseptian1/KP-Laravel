<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\NotificationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDepositController extends Controller
{
    public function monitoring(Request $request)
    {
        $validated = $request->validate([
            'server' => 'nullable|string|max:100',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $server = $validated['server'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $query = Deposit::query()->orderByDesc('created_at');

        if ($server) {
            $query->where('server', $server);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $items = $query->paginate(15)->withQueryString();
        $serverOptions = Deposit::query()
            ->whereNotNull('server')
            ->where('server', '!=', '')
            ->select('server')
            ->distinct()
            ->orderBy('server')
            ->pluck('server');

        return view('admin.deposit.monitoring', [
            'title' => 'Monitoring Deposit',
            'menuAdminDepositMonitoring' => 'active',
            'items' => $items,
            'server' => $server,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'serverOptions' => $serverOptions,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'reply_penambahan' => 'required|string',
            'status' => 'required|in:pending,approved,rejected,selesai',
        ]);

        $item = Deposit::findOrFail($id);
        $item->reply_penambahan = $validated['reply_penambahan'];
        $item->status = $validated['status'];
        $item->save();

        NotificationItem::create([
            'type' => 'deposit_request_updated',
            'reference_id' => $item->id,
            'message' => 'Status request deposit diperbarui: ' . $item->nama_supplier,
            'is_read' => false,
        ]);

        return redirect()->route('admin.deposit.monitoring')->with('success', 'Request deposit berhasil diperbarui');
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
        $item->nama_supplier = $validated['nama_supplier'];
        $item->jenis_transaksi = $validated['jenis_transaksi'];
        $item->nominal = $validated['nominal'];
        $item->bank = $validated['bank'];
        $item->server = $validated['server'];
        $item->no_rek = $validated['no_rek'];
        $item->nama_rekening = $validated['nama_rekening'];
        $item->reply_tiket = $validated['reply_tiket'] ?? null;
        $item->reply_penambahan = $validated['reply_penambahan'];
        $item->jam = $validated['jam'];
        $item->save();

        return redirect()->route('admin.deposit.monitoring')->with('success', 'Data request deposit berhasil diedit');
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,selesai',
        ]);

        $item = Deposit::findOrFail($id);
        $item->status = $validated['status'];
        $item->save();

        return redirect()->route('admin.deposit.monitoring')->with('success', 'Status request deposit berhasil diperbarui');
    }

    public function destroy(int $id)
    {
        $item = Deposit::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.deposit.monitoring')->with('success', 'Request deposit berhasil dihapus');
    }

    public function analysis(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->jabatan, ['Admin', 'HRD'], true)) {
            abort(403, 'Akses ditolak');
        }

        $summary = Deposit::selectRaw('COUNT(*) as total, SUM(nominal) as total_nominal, AVG(nominal) as avg_nominal, MIN(nominal) as min_nominal, MAX(nominal) as max_nominal')
            ->first();

        $byBank = Deposit::selectRaw('bank, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('bank')
            ->orderByDesc('total')
            ->get();

        $byServer = Deposit::selectRaw('server, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('server')
            ->orderByDesc('total')
            ->get();

        $bySupplier = Deposit::selectRaw('nama_supplier, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('nama_supplier')
            ->orderByDesc('total')
            ->get();

        $byAccount = Deposit::selectRaw('no_rek, nama_rekening, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('no_rek', 'nama_rekening')
            ->orderByDesc('total')
            ->get();

        $byHour = Deposit::selectRaw('HOUR(jam) as jam, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('jam')
            ->orderBy('jam')
            ->get();

        $replyCount = Deposit::whereNotNull('reply_penambahan')
            ->where('reply_penambahan', '!=', '')
            ->count();

        $byStatus = Deposit::selectRaw('status, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('status')
            ->orderByDesc('jumlah')
            ->get();

        return view('admin.deposit.analysis', [
            'title' => 'Analisis Deposit',
            'menuDepositAnalysis' => 'active',
            'summary' => $summary,
            'byBank' => $byBank,
            'byServer' => $byServer,
            'bySupplier' => $bySupplier,
            'byAccount' => $byAccount,
            'byHour' => $byHour,
            'replyCount' => $replyCount,
            'byStatus' => $byStatus,
        ]);
    }
}
