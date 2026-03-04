<?php

namespace App\Http\Controllers;

use App\Exports\StaffDepositRequestExport;
use App\Models\Bank;
use App\Models\Deposit;
use App\Models\DepositForm;
use App\Models\NotificationItem;
use App\Models\Server;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DepositFormController extends Controller
{
    private function getBankOptions()
    {
        return Bank::query()
            ->select('nama_bank')
            ->orderBy('nama_bank')
            ->pluck('nama_bank')
            ->map(fn ($bank) => trim((string) $bank))
            ->filter(fn ($bank) => $bank !== '')
            ->unique()
            ->sort()
            ->values();
    }

    private function buildStaffFilteredQuery(
        string $tanggal,
        string $searchSupplier,
        string $server,
        ?string $status,
        string $normalizedNominalFilter
    ) {
        return Deposit::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->where('is_deleted_by_staff', false)
                    ->orWhereNull('is_deleted_by_staff');
            })
            ->whereDate('created_at', $tanggal)
            ->when($server !== '', function ($q) use ($server) {
                $q->where('server', 'like', '%' . $server . '%');
            })
            ->when($searchSupplier !== '', function ($q) use ($searchSupplier) {
                $q->where('nama_supplier', 'like', '%' . $searchSupplier . '%');
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($normalizedNominalFilter !== '', function ($q) use ($normalizedNominalFilter) {
                $q->where('nominal', (float) $normalizedNominalFilter);
            });
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

    private function buildExportContext(Request $request): array
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'search_supplier' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'nominal' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $searchSupplier = trim((string) ($validated['search_supplier'] ?? ''));
        $server = trim((string) ($validated['server'] ?? ''));
        $status = $validated['status'] ?? null;
        $nominalFilter = trim((string) ($validated['nominal'] ?? ''));
        $normalizedNominalFilter = preg_replace('/[^0-9]/', '', $nominalFilter);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $baseQuery = $this->buildStaffFilteredQuery(
            $tanggal,
            $searchSupplier,
            $server,
            $status,
            $normalizedNominalFilter
        );

        $items = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $totalDeposit = (clone $baseQuery)
            ->where('jenis_transaksi', 'deposit')
            ->sum('nominal');

        $user = Auth::user();
        $staffEmail = strtolower((string) ($user->email ?? 'staff'));
        $safeEmail = preg_replace('/[^a-z0-9\._-]/i', '_', $staffEmail);

        return [
            'filters' => [
                'tanggal' => $tanggal,
                'search_supplier' => $searchSupplier,
                'server' => $server,
                'status' => $status,
                'nominal' => $nominalFilter,
            ],
            'items' => $items,
            'total_deposit' => (float) $totalDeposit,
            'downloaded_at' => now(),
            'staff_email' => $staffEmail,
            'safe_email' => $safeEmail,
        ];
    }

    public function exportExcel(Request $request)
    {
        $context = $this->buildExportContext($request);
        $tanggal = $context['filters']['tanggal'];

        return Excel::download(
            new StaffDepositRequestExport(
                $context['items'],
                $context['total_deposit'],
                $context['downloaded_at'],
                $context['staff_email']
            ),
            'Request-Deposit-' . $context['safe_email'] . '-' . $tanggal . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $context = $this->buildExportContext($request);
        $tanggal = $context['filters']['tanggal'];

        $pdf = Pdf::loadView('staff.deposit.pdf', [
            'items' => $context['items'],
            'filters' => $context['filters'],
            'totalDeposit' => $context['total_deposit'],
            'downloadedAt' => $context['downloaded_at'],
            'staffEmail' => $context['staff_email'],
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Request-Deposit-' . $context['safe_email'] . '-' . $tanggal . '.pdf');
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'search_supplier' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'nominal' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $searchSupplier = trim((string) ($validated['search_supplier'] ?? ''));
        $server = trim((string) ($validated['server'] ?? ''));
        $status = $validated['status'] ?? null;
        $nominalFilter = trim((string) ($validated['nominal'] ?? ''));
        $normalizedNominalFilter = preg_replace('/[^0-9]/', '', $nominalFilter);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $activeForms = DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('created_at')
            ->get();

        $baseQuery = $this->buildStaffFilteredQuery(
            $tanggal,
            $searchSupplier,
            $server,
            $status,
            $normalizedNominalFilter
        );

        $query = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ;

        $latestUpdatedAt = (clone $query)->max('updated_at');
        $latestActivityItem = (clone $query)->first();

        $items = $query->paginate($perPage)->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->pluck('nama_supplier');
        $servers = Server::orderBy('nama_server')->pluck('nama_server');
        $banks = $this->getBankOptions();
        $todayDepositSummary = (clone $baseQuery)
            ->where('jenis_transaksi', 'deposit')
            ->selectRaw('COUNT(*) as total_request, COALESCE(SUM(nominal), 0) as total_nominal')
            ->first();

        return view('staff.deposit.index', [
            'title' => 'Request Deposit',
            'menuDepositRequest' => 'active',
            'activeForms' => $activeForms,
            'items' => $items,
            'suppliers' => $suppliers,
            'servers' => $servers,
            'banks' => $banks,
            'tanggal' => $tanggal,
            'searchSupplier' => $searchSupplier,
            'serverFilter' => $server,
            'status' => $status,
            'nominalFilter' => $nominalFilter,
            'perPage' => $perPage,
            'latestUpdatedAt' => $latestUpdatedAt,
            'latestActivityItem' => $latestActivityItem,
            'todayDepositSummary' => $todayDepositSummary,
        ]);
    }

    public function changes(Request $request)
    {
        $validated = $request->validate([
            'since' => 'nullable|date',
            'tanggal' => 'nullable|date_format:Y-m-d',
            'search_supplier' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'nominal' => 'nullable|string|max:50',
        ]);

        $since = !empty($validated['since']) ? Carbon::parse($validated['since']) : now()->subMinutes(1);
        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $searchSupplier = trim((string) ($validated['search_supplier'] ?? ''));
        $server = trim((string) ($validated['server'] ?? ''));
        $status = $validated['status'] ?? null;
        $nominalFilter = trim((string) ($validated['nominal'] ?? ''));
        $normalizedNominalFilter = preg_replace('/[^0-9]/', '', $nominalFilter);

        $baseQuery = $this->buildStaffFilteredQuery(
            $tanggal,
            $searchSupplier,
            $server,
            $status,
            $normalizedNominalFilter
        );

        $query = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $changedQuery = (clone $query)
            ->where('updated_at', '>', $since);

        $changesCount = (clone $changedQuery)->count();

        $latestChangedItem = (clone $changedQuery)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $latestUpdatedAt = (clone $query)->max('updated_at');
        $latestActivityItem = (clone $query)->first();
        $todayDepositSummary = (clone $baseQuery)
            ->where('jenis_transaksi', 'deposit')
            ->selectRaw('COUNT(*) as total_request, COALESCE(SUM(nominal), 0) as total_nominal')
            ->first();

        $changeTitle = null;
        $changeDescription = null;
        $changedItemsPayload = [];

        if ($latestChangedItem) {
            $changeTitle = 'Ada perubahan Deposit ' . ($latestChangedItem->server ?: '-');

            $descriptions = [];

            if (!empty($latestChangedItem->status)) {
                $descriptions[] = 'Status: ' . ucfirst((string) $latestChangedItem->status);
            }

            if (!empty($latestChangedItem->reply_penambahan) && $latestChangedItem->reply_penambahan !== 'Menunggu Konfirmasi Admin') {
                $replyText = trim((string) $latestChangedItem->reply_penambahan);
                $descriptions[] = 'Bukti Penambahan: ' . mb_strimwidth($replyText, 0, 80, '...');
            }

            if (($latestChangedItem->bukti_transfer_admin_type ?? null) === 'text' && !empty($latestChangedItem->bukti_transfer_admin_text)) {
                $buktiText = trim((string) $latestChangedItem->bukti_transfer_admin_text);
                $descriptions[] = 'Bukti Transfer Admin: ' . mb_strimwidth($buktiText, 0, 80, '...');
            }

            if (($latestChangedItem->bukti_transfer_admin_type ?? null) === 'image' && !empty($latestChangedItem->bukti_transfer_admin_image)) {
                $descriptions[] = 'Bukti Transfer Admin: gambar diperbarui';
            }

            $changeDescription = !empty($descriptions)
                ? implode(' | ', $descriptions)
                : 'Ada perubahan data oleh admin.';
        }

        $changedItems = (clone $changedQuery)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get([
                'id',
                'nama_supplier',
                'nama_rekening',
                'reply_tiket',
                'reply_penambahan',
                'bukti_transfer_admin_type',
                'bukti_transfer_admin_text',
                'bukti_transfer_admin_image',
                'status',
                'jam',
                'updated_at',
            ]);

        foreach ($changedItems as $changedItem) {
            $changedItemsPayload[] = [
                'id' => $changedItem->id,
                'nama_supplier' => $changedItem->nama_supplier,
                'nama_rekening' => $changedItem->nama_rekening,
                'reply_tiket' => $changedItem->reply_tiket,
                'reply_penambahan' => $changedItem->reply_penambahan,
                'bukti_transfer_admin_type' => $changedItem->bukti_transfer_admin_type,
                'bukti_transfer_admin_text' => $changedItem->bukti_transfer_admin_text,
                'has_bukti_transfer_admin_image' => !empty($changedItem->bukti_transfer_admin_image),
                'status' => $changedItem->status,
                'jam' => $changedItem->jam ? Carbon::parse($changedItem->jam)->format('H:i') : '-',
                'updated_at' => $changedItem->updated_at,
            ];
        }

        return response()->json([
            'has_changes' => $changesCount > 0,
            'changes_count' => $changesCount,
            'latest_updated_at' => $latestUpdatedAt,
            'latest_card_html' => view('staff.deposit.partials.latest-activity-card', [
                'latestActivityItem' => $latestActivityItem,
            ])->render(),
            'today_total_card_html' => view('staff.deposit.partials.today-total-card', [
                'todayDepositSummary' => $todayDepositSummary,
            ])->render(),
            'change_title' => $changeTitle,
            'change_description' => $changeDescription,
            'changed_items' => $changedItemsPayload,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function viewTransferAdminImage(int $id)
    {
        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $path = $item->bukti_transfer_admin_image;
        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('deposit.request.index')->with('error', 'Bukti transfer admin tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }

    public function storeFromRequestPage(Request $request)
    {
        $this->normalizeNumericFields($request);

        $validated = $request->validate([
            'form_id' => 'nullable|exists:deposit_forms,id',
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => 'required|string|max:100|exists:banks,nama_bank',
            'server' => 'required|string|max:100|exists:servers,nama_server',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'reply_tiket_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $replyTiketImagePath = $request->hasFile('reply_tiket_image')
            ? $request->file('reply_tiket_image')->store('deposit/reply-tiket', 'local')
            : null;

        $formId = $validated['form_id'] ?? DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->value('id');

        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'form_id' => $formId,
            'nama_supplier' => $validated['nama_supplier'],
            'jenis_transaksi' => $validated['jenis_transaksi'],
            'nominal' => $validated['nominal'],
            'bank' => $validated['bank'],
            'server' => $validated['server'],
            'no_rek' => $validated['no_rek'],
            'nama_rekening' => $validated['nama_rekening'],
            'reply_tiket' => $validated['reply_tiket'] ?? null,
            'reply_tiket_image' => $replyTiketImagePath,
            'reply_penambahan' => 'Menunggu Konfirmasi Admin',
            'status' => 'pending',
            'jam' => now()->format('H:i'),
        ]);

        NotificationItem::create([
            'type' => 'deposit_request_submitted',
            'reference_id' => $deposit->id,
            'message' => 'Request deposit baru: ' . $deposit->nama_supplier,
            'is_read' => false,
        ]);

        return redirect()->route('deposit.request.index')->with('success', 'Request deposit berhasil dikirim');
    }

    public function show(string $token)
    {
        $form = DepositForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        return view('staff.deposit.form', [
            'title' => 'Form Deposit',
            'menuDeposit' => 'active',
            'form' => $form,
            'suppliers' => Supplier::orderBy('nama_supplier')->pluck('nama_supplier'),
            'servers' => Server::orderBy('nama_server')->pluck('nama_server'),
            'banks' => $this->getBankOptions(),
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $form = DepositForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        $this->normalizeNumericFields($request);

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:0',
            'bank' => 'required|string|max:100|exists:banks,nama_bank',
            'server' => 'required|string|max:100|exists:servers,nama_server',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
        ]);

        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'form_id' => $form->id,
            'nama_supplier' => $validated['nama_supplier'],
            'jenis_transaksi' => $validated['jenis_transaksi'],
            'nominal' => $validated['nominal'],
            'bank' => $validated['bank'],
            'server' => $validated['server'],
            'no_rek' => $validated['no_rek'],
            'nama_rekening' => $validated['nama_rekening'],
            'reply_tiket' => $validated['reply_tiket'] ?? null,
            'reply_penambahan' => 'Menunggu Konfirmasi Admin',
            'status' => 'pending',
            'jam' => now()->format('H:i'),
        ]);

        $adminNumber = config('whatsapp.admin_numbers.0') ?: '-';
        $replyText = "FORM ORDER H2H\n" .
            "Nama Suplier : {$deposit->nama_supplier}\n" .
            "Jenis        : " . strtoupper($deposit->jenis_transaksi) . "\n" .
            "Nominal      : " . number_format((float) $deposit->nominal, 0, ',', '.') . "\n" .
            "BANK         : {$deposit->bank}\n" .
            "SERVER       : {$deposit->server}\n" .
            "No. Rek      : {$deposit->no_rek}\n" .
            "Nama Rek     : {$deposit->nama_rekening}\n" .
            "Reply Tiket  : " . ($deposit->reply_tiket ?: '-') . "\n" .
            "Reply Admin  : " . ($deposit->reply_penambahan ?: '-') . "\n" .
            "Jam          : {$deposit->jam}\n\n" .
            "Note: kirimkan ke WhatsApp admin, jika ingin diproses {$adminNumber}";

        return redirect()->back()
            ->with('success', 'Deposit berhasil dikirim')
            ->with('deposit_submitted', true)
            ->with('deposit_reply_text', $replyText);
    }

    public function updateReplyPenambahan(Request $request, int $id)
    {
        $validated = $request->validate([
            'reply_penambahan_type' => 'required|in:text,image',
            'reply_penambahan' => 'nullable|string',
            'reply_penambahan_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (($item->status ?? 'pending') !== 'approved') {
            return redirect()->route('deposit.request.index')->with('error', 'Request belum approved oleh admin');
        }

        $type = $validated['reply_penambahan_type'];
        $textReply = trim((string) ($validated['reply_penambahan'] ?? ''));

        if ($type === 'image') {
            if ($request->hasFile('reply_penambahan_image')) {
                if ($item->reply_penambahan_image && Storage::disk('local')->exists($item->reply_penambahan_image)) {
                    Storage::disk('local')->delete($item->reply_penambahan_image);
                }

                $path = $request->file('reply_penambahan_image')->store('deposit/reply-penambahan', 'local');
                $item->reply_penambahan_image = $path;
            }

            if (!$item->reply_penambahan_image) {
                return redirect()->route('deposit.request.index')->with('error', 'Upload atau paste gambar reply penambahan terlebih dahulu.');
            }

            $item->reply_penambahan_type = 'image';
            $item->reply_penambahan = $textReply !== '' ? $textReply : 'Reply penambahan berupa gambar';
        } else {
            if ($textReply === '') {
                return redirect()->route('deposit.request.index')->with('error', 'Reply penambahan wajib diisi untuk tipe text.');
            }

            if ($item->reply_penambahan_image && Storage::disk('local')->exists($item->reply_penambahan_image)) {
                Storage::disk('local')->delete($item->reply_penambahan_image);
            }

            $item->reply_penambahan_type = 'text';
            $item->reply_penambahan_image = null;
            $item->reply_penambahan = $textReply;
        }

        $item->save();

        return redirect()->route('deposit.request.index')->with('success', 'Reply Penambahan berhasil diupdate');
    }

    public function markDeleted(Request $request, int $id)
    {
        $validated = $request->validate([
            'delete_note' => 'required|string|max:500',
        ], [
            'delete_note.required' => 'Alasan penghapusan wajib diisi.',
        ]);

        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (($item->status ?? 'pending') !== 'pending') {
            return redirect()->route('deposit.request.index')->with('error', 'Hanya request dengan status pending yang bisa dihapus oleh staff.');
        }

        $item->is_deleted_by_staff = true;
        $item->staff_deleted_note = trim((string) $validated['delete_note']);
        $item->staff_deleted_at = now();
        $item->save();

        NotificationItem::create([
            'type' => 'deposit_request_deleted_by_staff',
            'reference_id' => $item->id,
            'message' => 'Request deposit dihapus staff: ' . $item->nama_supplier,
            'is_read' => false,
        ]);

        return redirect()->route('deposit.request.index')->with('success', 'Request pending berhasil dihapus dari daftar staff dan tetap tercatat untuk admin.');
    }
}
