<?php

namespace App\Http\Controllers;

use App\Exports\DepositMonitoringExport;
use App\Imports\DepositManualImport;
use App\Models\Bank;
use App\Models\Deposit;
use App\Models\NotificationItem;
use App\Models\Server;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class AdminDepositController extends Controller
{
    private function getMonitoringOptionLists(): array
    {
        $serverOptions = Server::query()
            ->select('nama_server')
            ->orderBy('nama_server')
            ->pluck('nama_server')
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->sort()
            ->values();

        $bankOptions = Bank::query()
            ->select('nama_bank')
            ->orderBy('nama_bank')
            ->pluck('nama_bank')
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->sort()
            ->values();

        $supplierOptions = Supplier::query()
            ->select('nama_supplier')
            ->orderBy('nama_supplier')
            ->pluck('nama_supplier')
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->sort()
            ->values();

        return [
            'serverOptions' => $serverOptions,
            'bankOptions' => $bankOptions,
            'supplierOptions' => $supplierOptions,
        ];
    }

    private function normalizeNumericFields(Request $request): void
    {
        $rawNominal = (string) $request->input('nominal', '');
        $rawNoRek = (string) $request->input('no_rek', '');

        $normalizedNominal = preg_replace('/[^0-9]/', '', $rawNominal);
        $normalizedNoRek = preg_replace('/[^0-9]/', '', $rawNoRek);

        $request->merge([
            'nominal' => $normalizedNominal,
            'no_rek' => $normalizedNoRek,
        ]);
    }

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
                    ->back()
                    ->with('error', 'Upload berhasil dibaca, tetapi tidak ada baris valid yang bisa diinput. Cek format kolom file Excel Anda.');
            }

            return redirect()
                ->back()
                ->with('success', "Upload manual berhasil diproses. {$insertedRows} baris berhasil diinput dengan status selesai.");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Upload manual gagal diproses: ' . $e->getMessage());
        }
    }

    public function monitoring(Request $request)
    {
        $filters = $this->normalizedMonitoringFilters($request);

        $query = $this->buildMonitoringQuery($filters);
        $optionLists = $this->getMonitoringOptionLists();

        $perPage = $filters['per_page'] ?? 50;
        $items = $query->paginate($perPage)->withQueryString();
        $latestUpdatedAt = (clone $query)->max('updated_at');
        $latestIncomingAt = (clone $query)->max('created_at');
        $latestIncomingItem = (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
        $latestIncomingId = $latestIncomingItem?->id;

        $latestIncomingServer = trim((string) ($latestIncomingItem->server ?? ''));
        $latestIncomingServerColor = 'primary';
        if ($latestIncomingServer !== '') {
            $latestIncomingServerColor = Server::query()
                ->where('nama_server', $latestIncomingServer)
                ->value('card_color') ?: 'primary';
        }

        return view('admin.deposit.monitoring', [
            'title' => 'Monitoring Deposit',
            'menuAdminDepositMonitoring' => 'active',
            'items' => $items,
            'server' => $filters['server'] ?? null,
            'bank' => $filters['bank'] ?? null,
            'namaSupplier' => $filters['nama_supplier'] ?? null,
            'startDate' => $filters['start_date'] ?? null,
            'endDate' => $filters['end_date'] ?? null,
            'status' => $filters['status'] ?? null,
            'staffDeleted' => $filters['staff_deleted'] ?? null,
            'globalSearch' => $filters['global_search'] ?? null,
            'perPage' => $perPage,
            'latestUpdatedAt' => $latestUpdatedAt,
            'latestIncomingAt' => $latestIncomingAt,
            'latestIncomingId' => $latestIncomingId,
            'latestIncomingServer' => $latestIncomingServer,
            'latestIncomingServerColor' => $latestIncomingServerColor,
            'serverOptions' => $optionLists['serverOptions'],
            'bankOptions' => $optionLists['bankOptions'],
            'supplierOptions' => $optionLists['supplierOptions'],
        ]);
    }

    public function changes(Request $request)
    {
        $filters = $request->validate([
            'server' => 'nullable|string|max:100',
            'bank' => 'nullable|string|max:100',
            'nama_supplier' => 'nullable|string|max:255',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'staff_deleted' => 'nullable|in:yes,no',
            'since' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
        ]);

        $filters = $this->applyDefaultDateRange($filters);

        $query = $this->buildMonitoringQuery($filters);

        $latestUpdatedAt = (clone $query)->max('updated_at');
        $latestIncomingAt = (clone $query)->max('created_at');
        $latestIncomingItem = (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
        $latestIncomingId = $latestIncomingItem?->id;

        $latestIncomingServer = trim((string) ($latestIncomingItem->server ?? ''));
        $latestIncomingServerColor = 'primary';
        if ($latestIncomingServer !== '') {
            $latestIncomingServerColor = Server::query()
                ->where('nama_server', $latestIncomingServer)
                ->value('card_color') ?: 'primary';
        }

        $since = !empty($filters['since']) ? Carbon::parse($filters['since']) : null;
        $changesCount = 0;
        $latestChangedItem = null;

        if ($since) {
            $changedQuery = (clone $query)->where('updated_at', '>', $since);
            $changesCount = (clone $changedQuery)->count();
            $latestChangedItem = (clone $changedQuery)->orderByDesc('updated_at')->first();
        }

        $hasChanges = $since ? $changesCount > 0 : false;

        $changeTitle = null;
        $changeDescription = null;

        if ($latestChangedItem) {
            $changeTitle = 'Ada perubahan Deposit ' . ($latestChangedItem->server ?: '-');
            $descriptions = [];

            if (!empty($latestChangedItem->status)) {
                $descriptions[] = 'Status: ' . ucfirst((string) $latestChangedItem->status);
            }
            if (!empty($latestChangedItem->reply_penambahan) && $latestChangedItem->reply_penambahan !== 'Menunggu Konfirmasi Admin') {
                $descriptions[] = 'Bukti Penambahan: ' . mb_strimwidth(trim((string) $latestChangedItem->reply_penambahan), 0, 80, '...');
            }
            if (($latestChangedItem->bukti_transfer_admin_type ?? null) === 'text' && !empty($latestChangedItem->bukti_transfer_admin_text)) {
                $descriptions[] = 'Bukti Transfer Admin: ' . mb_strimwidth(trim((string) $latestChangedItem->bukti_transfer_admin_text), 0, 80, '...');
            }
            if (($latestChangedItem->bukti_transfer_admin_type ?? null) === 'image' && !empty($latestChangedItem->bukti_transfer_admin_image)) {
                $descriptions[] = 'Bukti Transfer Admin: gambar diperbarui';
            }

            $changeDescription = !empty($descriptions)
                ? implode(' | ', $descriptions)
                : 'Ada perubahan data oleh admin.';
        }

        $tableHtml = null;
        $latestCardHtml = null;

        if ($hasChanges) {
            $optionLists = $this->getMonitoringOptionLists();
            $page = $filters['page'] ?? 1;
            $items = (clone $query)->paginate(15, ['*'], 'page', $page)->withQueryString();
            $tableHtml = view('admin.deposit.partials.table', [
                'items' => $items,
                'latestIncomingId' => $latestIncomingId,
                'latestIncomingServerColor' => $latestIncomingServerColor,
                'serverOptions' => $optionLists['serverOptions'],
                'bankOptions' => $optionLists['bankOptions'],
                'supplierOptions' => $optionLists['supplierOptions'],
            ])->render();
            $latestCardHtml = view('admin.deposit.partials.latest-incoming-card', [
                'latestIncomingAt' => $latestIncomingAt,
                'latestIncomingServer' => $latestIncomingServer,
                'latestIncomingServerColor' => $latestIncomingServerColor,
            ])->render();
        }

        return response()->json([
            'has_changes' => $hasChanges,
            'changes_count' => $changesCount,
            'latest_updated_at' => $latestUpdatedAt,
            'latest_incoming_at' => $latestIncomingAt,
            'latest_incoming_id' => $latestIncomingId,
            'change_title' => $changeTitle,
            'change_description' => $changeDescription,
            'table_html' => $tableHtml,
            'latest_card_html' => $latestCardHtml,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    private function buildMonitoringQuery(array $filters)
    {
        $query = Deposit::query()->orderByDesc('created_at')->orderByDesc('id');

        $server = $filters['server'] ?? null;
        $bank = $filters['bank'] ?? null;
        $namaSupplier = $filters['nama_supplier'] ?? null;
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $status = $filters['status'] ?? null;
        $staffDeleted = $filters['staff_deleted'] ?? null;
        $globalSearch = $filters['global_search'] ?? null;

        if ($server) {
            $query->where('server', 'like', "%{$server}%");
        }

        if ($bank) {
            $query->where('bank', 'like', "%{$bank}%");
        }

        if ($namaSupplier) {
            $query->where('nama_supplier', 'like', "%{$namaSupplier}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($staffDeleted === 'yes') {
            $query->where('is_deleted_by_staff', true);
        } elseif ($staffDeleted === 'no') {
            $query->where('is_deleted_by_staff', false);
        }

        if ($globalSearch) {
            $query->where(function ($q) use ($globalSearch) {
                $q->where('nama_pengirim', 'like', "%{$globalSearch}%")
                  ->orWhere('nominal', 'like', "%{$globalSearch}%")
                  ->orWhere('berita', 'like', "%{$globalSearch}%")
                  ->orWhere('no_rekening', 'like', "%{$globalSearch}%")
                  ->orWhere('kategori', 'like', "%{$globalSearch}%")
                  ->orWhere('alasan_reject', 'like', "%{$globalSearch}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        } elseif ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $query->whereTime('jam', '>=', '00:00:00')->whereTime('jam', '<=', '23:59:59');

        return $query;
    }

    private function normalizedMonitoringFilters(Request $request): array
    {
        $filters = $request->validate([
            'server' => 'nullable|string|max:100',
            'bank' => 'nullable|string|max:100',
            'nama_supplier' => 'nullable|string|max:255',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'staff_deleted' => 'nullable|in:yes,no',
            'per_page' => 'nullable|integer|in:10,50,100,200',
            'global_search' => 'nullable|string|max:255',
        ]);

        return $this->applyDefaultDateRange($filters);
    }

    private function applyDefaultDateRange(array $filters): array
    {
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            $today = now()->format('Y-m-d');
            $filters['start_date'] = $today;
            $filters['end_date'] = $today;
        }

        return $filters;
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->normalizedMonitoringFilters($request);
        $items = $this->buildMonitoringQuery($filters)->get();

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $rangeLabel = $startDate === $endDate ? $startDate : ($startDate . '_to_' . $endDate);

        return Excel::download(
            new DepositMonitoringExport($items),
            'Monitoring-Deposit-' . $rangeLabel . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->normalizedMonitoringFilters($request);
        $items = $this->buildMonitoringQuery($filters)->get();

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $rangeLabel = $startDate === $endDate ? $startDate : ($startDate . ' s.d. ' . $endDate);

        $pdf = Pdf::loadView('admin.deposit.pdf', [
            'items' => $items,
            'filters' => $filters,
            'rangeLabel' => $rangeLabel,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Monitoring-Deposit-' . str_replace(' ', '-', $rangeLabel) . '.pdf');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'reply_penambahan' => 'nullable|string',
            'bukti_transfer_admin_type' => 'nullable|in:text,image',
            'bukti_transfer_admin_text' => 'nullable|string',
            'bukti_transfer_admin_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'required|in:pending,approved,rejected,selesai',
        ]);

        $item = Deposit::findOrFail($id);
        $item->reply_penambahan = trim((string) ($validated['reply_penambahan'] ?? '')) !== ''
            ? trim((string) $validated['reply_penambahan'])
            : $item->reply_penambahan;
        $this->applyBuktiTransferAdmin($request, $item, $validated);
        $item->status = $validated['status'];
        $item->save();

        NotificationItem::create([
            'type' => 'deposit_request_updated',
            'reference_id' => $item->id,
            'message' => 'Status request deposit diperbarui: ' . $item->nama_supplier,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Request deposit berhasil diperbarui');
    }

    public function updateDetails(Request $request, int $id)
    {
        $this->normalizeNumericFields($request);

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'reply_penambahan' => 'nullable|string',
            'bukti_transfer_admin_type' => 'nullable|in:text,image',
            'bukti_transfer_admin_text' => 'nullable|string',
            'bukti_transfer_admin_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'jam' => 'required|date_format:H:i',
            'status' => 'required|in:approved,rejected,selesai,pending',
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
        $item->reply_penambahan = trim((string) ($validated['reply_penambahan'] ?? '')) !== ''
            ? trim((string) $validated['reply_penambahan'])
            : null;
        $this->applyBuktiTransferAdmin($request, $item, $validated);
        $item->jam = $validated['jam'];
        $item->status = $validated['status'];
        $item->save();

        return redirect()->back()->with('success', 'Data request deposit dan status berhasil diperbarui');
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,selesai',
        ]);

        $item = Deposit::findOrFail($id);
        $item->status = $validated['status'];
        $item->save();

        return redirect()->back()->with('success', 'Status request deposit berhasil diperbarui');
    }

    public function viewReplyImage(int $id)
    {
        $item = Deposit::findOrFail($id);
        $path = $item->reply_penambahan_image;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.deposit.monitoring')->with('error', 'Gambar reply penambahan tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function viewReplyTiketImage(int $id)
    {
        $item = Deposit::findOrFail($id);
        $path = $item->reply_tiket_image;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.deposit.monitoring')->with('error', 'Gambar reply tiket tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function viewTransferAdminImage(int $id)
    {
        $item = Deposit::findOrFail($id);
        $path = $item->bukti_transfer_admin_image;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.deposit.monitoring')->with('error', 'Gambar bukti transfer admin tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function destroy(int $id)
    {
        $item = Deposit::findOrFail($id);

        if ($item->reply_penambahan_image && Storage::disk('local')->exists($item->reply_penambahan_image)) {
            Storage::disk('local')->delete($item->reply_penambahan_image);
        }

        if ($item->reply_tiket_image && Storage::disk('local')->exists($item->reply_tiket_image)) {
            Storage::disk('local')->delete($item->reply_tiket_image);
        }

        if ($item->bukti_transfer_admin_image && Storage::disk('local')->exists($item->bukti_transfer_admin_image)) {
            Storage::disk('local')->delete($item->bukti_transfer_admin_image);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Request deposit berhasil dihapus');
    }

    private function applyBuktiTransferAdmin(Request $request, Deposit $item, array $validated): void
    {
        $type = $validated['bukti_transfer_admin_type'] ?? ($request->hasFile('bukti_transfer_admin_image') ? 'image' : 'text');
        $textReply = trim((string) ($validated['bukti_transfer_admin_text'] ?? ''));

        if ($type === 'image') {
            if ($request->hasFile('bukti_transfer_admin_image')) {
                if ($item->bukti_transfer_admin_image && Storage::disk('local')->exists($item->bukti_transfer_admin_image)) {
                    Storage::disk('local')->delete($item->bukti_transfer_admin_image);
                }

                $path = $request->file('bukti_transfer_admin_image')->store('deposit/bukti-transfer-admin', 'local');
                $item->bukti_transfer_admin_image = $path;
            }

            if (!$item->bukti_transfer_admin_image) {
                throw ValidationException::withMessages([
                    'bukti_transfer_admin_image' => 'Pilih atau paste gambar bukti transfer admin terlebih dahulu untuk tipe image.',
                ]);
            }

            $item->bukti_transfer_admin_type = 'image';
            $item->bukti_transfer_admin_text = $textReply !== '' ? $textReply : null;
            return;
        }

        if ($textReply === '') {
            throw ValidationException::withMessages([
                'bukti_transfer_admin_text' => 'Input bukti transfer admin wajib diisi untuk tipe text.',
            ]);
        }

        if ($item->bukti_transfer_admin_image && Storage::disk('local')->exists($item->bukti_transfer_admin_image)) {
            Storage::disk('local')->delete($item->bukti_transfer_admin_image);
            $item->bukti_transfer_admin_image = null;
        }

        $item->bukti_transfer_admin_type = 'text';
        $item->bukti_transfer_admin_text = $textReply;
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
