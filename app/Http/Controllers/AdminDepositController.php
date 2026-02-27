<?php

namespace App\Http\Controllers;

use App\Imports\DepositManualImport;
use App\Models\Deposit;
use App\Models\NotificationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminDepositController extends Controller
{
    public function importManual(Request $request)
    {
        $validated = $request->validate([
            'manual_date' => 'required|date_format:Y-m-d',
            'manual_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'manual_date.required' => 'Tanggal input wajib dipilih.',
            'manual_date.date_format' => 'Format tanggal input tidak valid.',
            'manual_file.required' => 'File Excel wajib dipilih.',
            'manual_file.mimes' => 'File harus berformat xlsx, xls, csv, atau txt.',
            'manual_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $importer = new DepositManualImport($validated['manual_date'], Auth::id());

            Excel::import(
                $importer,
                $request->file('manual_file')
            );

            $insertedRows = $importer->getInsertedCount();

            if ($insertedRows < 1) {
                return redirect()
                    ->route('admin.deposit.monitoring')
                    ->with('error', 'Upload berhasil dibaca, tetapi tidak ada baris valid yang bisa diinput. Cek format kolom file Excel Anda.');
            }

            return redirect()
                ->route('admin.deposit.monitoring')
                ->with('success', "Upload manual berhasil diproses. {$insertedRows} baris berhasil diinput dengan status selesai.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.deposit.monitoring')
                ->with('error', 'Upload manual gagal diproses: ' . $e->getMessage());
        }
    }

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
            $query->where('server', 'like', "%{$server}%");
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

        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'server' => 'nullable|string|max:100',
            'bank' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'nama_supplier' => 'nullable|string|max:255',
            'jenis_transaksi' => 'nullable|in:deposit,hutang',
        ]);

        $filters = [
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'server' => $validated['server'] ?? null,
            'bank' => $validated['bank'] ?? null,
            'status' => $validated['status'] ?? null,
            'nama_supplier' => $validated['nama_supplier'] ?? null,
            'jenis_transaksi' => $validated['jenis_transaksi'] ?? null,
        ];

        $baseQuery = Deposit::query();

        if ($filters['start_date']) {
            $baseQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $baseQuery->whereDate('created_at', '<=', $filters['end_date']);
        }
        if ($filters['server']) {
            $baseQuery->where('server', $filters['server']);
        }
        if ($filters['bank']) {
            $baseQuery->where('bank', $filters['bank']);
        }
        if ($filters['status']) {
            $baseQuery->where('status', $filters['status']);
        }
        if ($filters['nama_supplier']) {
            $baseQuery->where('nama_supplier', $filters['nama_supplier']);
        }
        if ($filters['jenis_transaksi']) {
            $baseQuery->where('jenis_transaksi', $filters['jenis_transaksi']);
        }

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(nominal), 0) as total_nominal')
            ->selectRaw('COALESCE(AVG(nominal), 0) as avg_nominal')
            ->selectRaw('COALESCE(MIN(nominal), 0) as min_nominal')
            ->selectRaw('COALESCE(MAX(nominal), 0) as max_nominal')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as total_pending')
            ->selectRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as total_approved')
            ->selectRaw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as total_rejected')
            ->selectRaw('SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as total_selesai')
            ->first();

        $byBank = (clone $baseQuery)
            ->selectRaw('bank, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('bank')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byServer = (clone $baseQuery)
            ->selectRaw('server, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('server')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $bySupplier = (clone $baseQuery)
            ->selectRaw('nama_supplier, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('nama_supplier')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byAccount = (clone $baseQuery)
            ->selectRaw('no_rek, nama_rekening, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('no_rek', 'nama_rekening')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byHour = (clone $baseQuery)
            ->selectRaw('HOUR(jam) as jam, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy(DB::raw('HOUR(jam)'))
            ->orderBy('jam')
            ->get();

        $byStatus = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('status')
            ->orderByDesc('jumlah')
            ->get();

        $trendDaily = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('tanggal')
            ->limit(31)
            ->get();

        $replyCount = (clone $baseQuery)
            ->whereNotNull('reply_penambahan')
            ->where('reply_penambahan', '!=', '')
            ->count();

        $activeDays = (clone $baseQuery)
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as total_hari')
            ->value('total_hari') ?? 0;

        $period = (clone $baseQuery)
            ->selectRaw('MIN(DATE(created_at)) as min_date, MAX(DATE(created_at)) as max_date')
            ->first();

        $approvalRate = ($summary->total ?? 0) > 0
            ? round((($summary->total_approved ?? 0) / $summary->total) * 100, 2)
            : 0;

        $completionRate = ($summary->total ?? 0) > 0
            ? round((($summary->total_selesai ?? 0) / $summary->total) * 100, 2)
            : 0;

        $rejectionRate = ($summary->total ?? 0) > 0
            ? round((($summary->total_rejected ?? 0) / $summary->total) * 100, 2)
            : 0;

        $avgPerDay = $activeDays > 0
            ? ($summary->total_nominal / $activeDays)
            : 0;

        $topBank = $byBank->first();
        $topServer = $byServer->first();
        $topSupplier = $bySupplier->first();

        $filterOptions = [
            'servers' => Deposit::query()->whereNotNull('server')->where('server', '!=', '')->distinct()->orderBy('server')->pluck('server'),
            'banks' => Deposit::query()->whereNotNull('bank')->where('bank', '!=', '')->distinct()->orderBy('bank')->pluck('bank'),
            'suppliers' => Deposit::query()->whereNotNull('nama_supplier')->where('nama_supplier', '!=', '')->distinct()->orderBy('nama_supplier')->pluck('nama_supplier'),
        ];

        return view('admin.deposit.analysis', [
            'title' => 'Analisis Deposit',
            'menuDepositAnalysis' => 'active',
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'summary' => $summary,
            'byBank' => $byBank,
            'byServer' => $byServer,
            'bySupplier' => $bySupplier,
            'byAccount' => $byAccount,
            'byHour' => $byHour,
            'replyCount' => $replyCount,
            'byStatus' => $byStatus,
            'trendDaily' => $trendDaily,
            'activeDays' => $activeDays,
            'approvalRate' => $approvalRate,
            'completionRate' => $completionRate,
            'rejectionRate' => $rejectionRate,
            'avgPerDay' => $avgPerDay,
            'topBank' => $topBank,
            'topServer' => $topServer,
            'topSupplier' => $topSupplier,
            'period' => $period,
        ]);
    }
}
