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
        $items = Deposit::orderByDesc('created_at')->paginate(15);

        return view('admin.deposit.monitoring', [
            'title' => 'Monitoring Deposit',
            'menuAdminDepositMonitoring' => 'active',
            'items' => $items,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'reply_penambahan' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
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
