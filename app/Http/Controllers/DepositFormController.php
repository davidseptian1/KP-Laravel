<?php

namespace App\Http\Controllers;

use App\Exports\DepositMonitoringExport;
use App\Models\Bank;
use App\Models\Deposit;
use App\Models\DepositForm;
use App\Models\NotificationItem;
use App\Models\Server;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DepositFormController extends Controller
{
    private function bankValidationRules(): array
    {
        $rules = ['required', 'string', 'max:100'];

        if (Bank::query()->exists()) {
            $rules[] = 'exists:banks,nama_bank';
        }

        return $rules;
    }

    private function buildStaffDepositQuery(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $serverFilter = (string) $request->input('server', '');
        $status = (string) $request->input('status', '');
        $searchSupplier = (string) $request->input('search_supplier', '');
        $nominalFilter = (string) $request->input('nominal', '');

        $query = Deposit::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('is_deleted_by_staff')
                    ->orWhere('is_deleted_by_staff', false);
            })
            ->whereDate('created_at', $tanggal);

        if ($serverFilter !== '') {
            $query->where('server', $serverFilter);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($searchSupplier !== '') {
            $query->where('nama_supplier', 'like', '%' . $searchSupplier . '%');
        }

        if ($nominalFilter !== '') {
            $normalizedNominal = preg_replace('/[^0-9]/', '', $nominalFilter);
            if ($normalizedNominal !== '') {
                $query->where('nominal', (float) $normalizedNominal);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $serverFilter = $request->input('server', '');
        $status = $request->input('status', '');
        $searchSupplier = $request->input('search_supplier', '');
        $nominalFilter = $request->input('nominal', '');
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $activeForms = DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('created_at')
            ->get();

        $itemsQuery = $this->buildStaffDepositQuery($request);

        $latestUpdatedAt = (clone $itemsQuery)->max('updated_at');
        $latestActivityItem = (clone $itemsQuery)->orderByDesc('updated_at')->orderByDesc('id')->first();
        $todayDepositSummary = (clone $itemsQuery)
            ->selectRaw('COUNT(*) as total_request, COALESCE(SUM(nominal), 0) as total_nominal')
            ->first();

        $items = $itemsQuery
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->pluck('nama_supplier');

        // Filter banks: if user is Admin or Superadmin show all banks, otherwise show banks assigned to the user or to ALL
        $banksQuery = Bank::query();
        $current = Auth::user();
        if ($current && in_array($current->jabatan, ['Admin', 'Superadmin'])) {
            $banksQuery = $banksQuery;
        } else if ($current) {
            $email = $current->email;
            $banksQuery = $banksQuery->where(function ($q) use ($email) {
                $q->where('user_email', 'ALL')->orWhere('user_email', $email);
            });
        } else {
            // not authenticated, default to banks assigned to ALL
            $banksQuery = $banksQuery->where('user_email', 'ALL');
        }

        $banks = $banksQuery->orderBy('nama_bank')->pluck('nama_bank');

        // Filter servers similarly: Admin/Superadmin see all, other users see servers assigned to them or ALL
        $serversQuery = Server::query();
        if ($current && in_array($current->jabatan, ['Admin', 'Superadmin'])) {
            $serversQuery = $serversQuery;
        } else if ($current) {
            $email = $current->email;
            $serversQuery = $serversQuery->where(function ($q) use ($email) {
                $q->where('user_email', 'ALL')->orWhere('user_email', $email);
            });
        } else {
            $serversQuery = $serversQuery->where('user_email', 'ALL');
        }

        $servers = $serversQuery->orderBy('nama_server')->pluck('nama_server');

        // determine default server for the current user: prefer a server explicitly assigned to the user's email
        $defaultServer = null;
        if ($current && !in_array($current->jabatan, ['Admin', 'Superadmin'])) {
            $assigned = Server::where('user_email', $current->email)->orderBy('nama_server')->first();
            if ($assigned) {
                $defaultServer = $assigned->nama_server;
            } elseif ($servers->count() === 1) {
                // if only one available server for this user (e.g. only ALL or single), default to it
                $defaultServer = $servers->first();
            }
        }

        return view('staff.deposit.index', [
            'title' => 'Request Deposit',
            'menuDepositRequest' => 'active',
            'activeForms' => $activeForms,
            'items' => $items,
            'suppliers' => $suppliers,
            'banks' => $banks,
            'servers' => $servers,
            'tanggal' => $tanggal,
            'serverFilter' => $serverFilter,
            'status' => $status,
            'searchSupplier' => $searchSupplier,
            'nominalFilter' => $nominalFilter,
            'perPage' => $perPage,
            'latestUpdatedAt' => $latestUpdatedAt,
            'latestActivityItem' => $latestActivityItem,
            'todayDepositSummary' => $todayDepositSummary,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $items = $this->buildStaffDepositQuery($request)
            ->orderByDesc('created_at')
            ->get();

        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));

        return Excel::download(
            new DepositMonitoringExport($items),
            'Request-Deposit-' . $tanggal . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $items = $this->buildStaffDepositQuery($request)
            ->orderByDesc('created_at')
            ->get();

        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $rangeLabel = $tanggal;

        $pdf = Pdf::loadView('admin.deposit.pdf', [
            'items' => $items,
            'rangeLabel' => $rangeLabel,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Request-Deposit-' . $tanggal . '.pdf');
    }

    public function changes(Request $request)
    {
        $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'server' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected,selesai',
            'search_supplier' => 'nullable|string|max:255',
            'nominal' => 'nullable|string|max:50',
            'since' => 'nullable|date',
        ]);

        $query = $this->buildStaffDepositQuery($request);

        $latestUpdatedAt = (clone $query)->max('updated_at');
        $latestActivityItem = (clone $query)->orderByDesc('updated_at')->orderByDesc('id')->first();
        $todayDepositSummary = (clone $query)
            ->selectRaw('COUNT(*) as total_request, COALESCE(SUM(nominal), 0) as total_nominal')
            ->first();

        $since = $request->filled('since') ? Carbon::parse((string) $request->input('since')) : null;
        $changedItems = collect();

        if ($since) {
            $changedItems = (clone $query)
                ->where('updated_at', '>', $since)
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();
        }

        $hasChanges = $since ? $changedItems->isNotEmpty() : false;

        $latestChangedItem = $changedItems->first();
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

        $latestCardHtml = null;
        $todayTotalCardHtml = null;

        if ($hasChanges) {
            $latestCardHtml = view('staff.deposit.partials.latest-activity-card', [
                'latestActivityItem' => $latestActivityItem,
            ])->render();

            $todayTotalCardHtml = view('staff.deposit.partials.today-total-card', [
                'todayDepositSummary' => $todayDepositSummary,
            ])->render();
        }

        return response()->json([
            'has_changes' => $hasChanges,
            'changes_count' => $changedItems->count(),
            'latest_updated_at' => $latestUpdatedAt,
            'change_title' => $changeTitle,
            'change_description' => $changeDescription,
            'latest_card_html' => $latestCardHtml,
            'today_total_card_html' => $todayTotalCardHtml,
            'changed_items' => $changedItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_supplier' => $item->nama_supplier,
                    'nama_rekening' => $item->nama_rekening,
                    'reply_tiket' => $item->reply_tiket,
                    'reply_penambahan' => $item->reply_penambahan,
                    'bukti_transfer_admin_type' => $item->bukti_transfer_admin_type,
                    'bukti_transfer_admin_text' => $item->bukti_transfer_admin_text,
                    'has_bukti_transfer_admin_image' => !empty($item->bukti_transfer_admin_image),
                    'status' => $item->status,
                    'jam' => $item->jam ? Carbon::parse((string) $item->jam)->format('H:i') : null,
                ];
            })->values(),
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    public function storeFromRequestPage(Request $request)
    {
        $bankRules = $this->bankValidationRules();

        $validated = $request->validate([
            'form_id' => 'nullable|exists:deposit_forms,id',
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => $bankRules,
            'bank_tujuan' => 'nullable|string|max:100',
            'server' => 'required|string|max:100|exists:servers,nama_server',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'jam' => 'nullable|date_format:H:i',
        ]);

        // enforce that the selected bank is allowed for this user (unless Admin/Superadmin)
        $current = Auth::user();
        if ($current && !in_array($current->jabatan, ['Admin', 'Superadmin'])) {
            $bankRow = Bank::where('nama_bank', $validated['bank'])->first();
            if (!$bankRow || ($bankRow->user_email !== 'ALL' && $bankRow->user_email !== $current->email)) {
                throw ValidationException::withMessages(['bank' => 'Bank yang dipilih tidak tersedia untuk akun Anda.']);
            }
        }

        // enforce that the selected server is allowed for this user (unless Admin/Superadmin)
        if ($current && !in_array($current->jabatan, ['Admin', 'Superadmin'])) {
            $serverRow = Server::where('nama_server', $validated['server'])->first();
            if (!$serverRow || ($serverRow->user_email !== 'ALL' && $serverRow->user_email !== $current->email)) {
                throw ValidationException::withMessages(['server' => 'Server yang dipilih tidak tersedia untuk akun Anda.']);
            }
        }
        $formId = $validated['form_id'] ?? DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->value('id');

        $depositPayload = [
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
            'reply_penambahan' => 'Menunggu Konfirmasi Admin',
            'status' => 'pending',
            'jam' => $validated['jam'] ?? now()->format('H:i'),
        ];

        if (Schema::hasColumn('deposits', 'bank_tujuan')) {
            $depositPayload['bank_tujuan'] = trim((string) ($validated['bank_tujuan'] ?? '')) !== ''
                ? trim((string) $validated['bank_tujuan'])
                : null;
        }

        $deposit = Deposit::create($depositPayload);

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
            'banks' => Bank::orderBy('nama_bank')->pluck('nama_bank'),
            'servers' => Server::orderBy('nama_server')->pluck('nama_server'),
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $bankRules = $this->bankValidationRules();

        $form = DepositForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:0',
            'bank' => $bankRules,
            'bank_tujuan' => 'nullable|string|max:100',
            'server' => 'required|string|max:100|exists:servers,nama_server',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'jam' => 'nullable|date_format:H:i',
        ]);

        $depositPayload = [
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
            'jam' => $validated['jam'] ?? now()->format('H:i'),
        ];

        if (Schema::hasColumn('deposits', 'bank_tujuan')) {
            $depositPayload['bank_tujuan'] = trim((string) ($validated['bank_tujuan'] ?? '')) !== ''
                ? trim((string) $validated['bank_tujuan'])
                : null;
        }

        $deposit = Deposit::create($depositPayload);

        $adminNumber = config('whatsapp.admin_numbers.0') ?: '-';
        $replyText = "FORM ORDER H2H\n" .
            "Nama Suplier : {$deposit->nama_supplier}\n" .
            "Jenis        : " . strtoupper($deposit->jenis_transaksi) . "\n" .
            "Nominal      : " . number_format((float) $deposit->nominal, 0, ',', '.') . "\n" .
            "BANK         : {$deposit->bank}\n" .
            "BANK TUJUAN  : " . ($deposit->bank_tujuan ?: '-') . "\n" .
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
            'reply_penambahan_type' => 'nullable|in:text,image',
            'reply_penambahan' => 'nullable|string',
            'reply_penambahan_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (($item->status ?? 'pending') !== 'approved') {
            return redirect()->route('deposit.request.index')->with('error', 'Request belum approved oleh admin');
        }

        $replyType = $validated['reply_penambahan_type'] ?? ($request->hasFile('reply_penambahan_image') ? 'image' : 'text');
        $replyText = trim((string) ($validated['reply_penambahan'] ?? ''));

        if ($replyType === 'image') {
            if ($request->hasFile('reply_penambahan_image')) {
                if ($item->reply_penambahan_image && Storage::disk('local')->exists($item->reply_penambahan_image)) {
                    Storage::disk('local')->delete($item->reply_penambahan_image);
                }

                $path = $request->file('reply_penambahan_image')->store('deposit/reply-penambahan', 'local');
                $item->reply_penambahan_image = $path;
            }

            if (!$item->reply_penambahan_image) {
                throw ValidationException::withMessages([
                    'reply_penambahan_image' => 'Pilih atau paste gambar reply penambahan terlebih dahulu untuk tipe image.',
                ]);
            }

            $item->reply_penambahan_type = 'image';
            $item->reply_penambahan = $replyText !== '' ? $replyText : null;
            $item->save();

            return redirect()->route('deposit.request.index')->with('success', 'Reply Penambahan berhasil diupdate');
        }

        if ($replyText === '') {
            throw ValidationException::withMessages([
                'reply_penambahan' => 'Reply penambahan wajib diisi untuk tipe text.',
            ]);
        }

        if ($item->reply_penambahan_image && Storage::disk('local')->exists($item->reply_penambahan_image)) {
            Storage::disk('local')->delete($item->reply_penambahan_image);
            $item->reply_penambahan_image = null;
        }

        $item->reply_penambahan_type = 'text';
        $item->reply_penambahan = $replyText;
        $item->save();

        return redirect()->route('deposit.request.index')->with('success', 'Reply Penambahan berhasil diupdate');
    }

    public function deletePending(Request $request, int $id)
    {
        $validated = $request->validate([
            'delete_note' => 'required|string|max:1000',
        ]);

        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (($item->status ?? 'pending') !== 'pending') {
            return redirect()->route('deposit.request.index')
                ->with('error', 'Hanya request dengan status pending yang bisa dihapus dari daftar staff');
        }

        $item->is_deleted_by_staff = true;
        $item->staff_deleted_note = trim((string) $validated['delete_note']);
        $item->staff_deleted_at = now();
        $item->save();

        return redirect()->route('deposit.request.index')
            ->with('success', 'Request pending berhasil dihapus dari daftar staff');
    }

    public function viewTransferAdminImage(int $id)
    {
        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $path = $item->bukti_transfer_admin_image;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()->route('deposit.request.index')
                ->with('error', 'Gambar bukti transfer admin tidak ditemukan');
        }

        return Storage::disk('local')->response($path);
    }
}
