<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositForm;
use App\Models\NotificationItem;
use App\Models\Server;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DepositFormController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'search_supplier' => 'nullable|string|max:255',
        ]);

        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $searchSupplier = trim((string) ($validated['search_supplier'] ?? ''));

        $activeForms = DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('created_at')
            ->get();

        $query = Deposit::where('user_id', Auth::id())
            ->whereDate('created_at', $tanggal)
            ->when($searchSupplier !== '', function ($q) use ($searchSupplier) {
                $q->where('nama_supplier', 'like', '%' . $searchSupplier . '%');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ;

        $latestUpdatedAt = (clone $query)->max('updated_at');

        $items = $query->paginate(10)->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->pluck('nama_supplier');
        $servers = Server::orderBy('nama_server')->pluck('nama_server');

        return view('staff.deposit.index', [
            'title' => 'Request Deposit',
            'menuDepositRequest' => 'active',
            'activeForms' => $activeForms,
            'items' => $items,
            'suppliers' => $suppliers,
            'servers' => $servers,
            'tanggal' => $tanggal,
            'searchSupplier' => $searchSupplier,
            'latestUpdatedAt' => $latestUpdatedAt,
        ]);
    }

    public function changes(Request $request)
    {
        $validated = $request->validate([
            'since' => 'nullable|date',
            'tanggal' => 'nullable|date_format:Y-m-d',
            'search_supplier' => 'nullable|string|max:255',
        ]);

        $since = !empty($validated['since']) ? Carbon::parse($validated['since']) : now()->subMinutes(1);
        $tanggal = $validated['tanggal'] ?? now()->format('Y-m-d');
        $searchSupplier = trim((string) ($validated['search_supplier'] ?? ''));

        $query = Deposit::where('user_id', Auth::id())
            ->whereDate('created_at', $tanggal)
            ->when($searchSupplier !== '', function ($q) use ($searchSupplier) {
                $q->where('nama_supplier', 'like', '%' . $searchSupplier . '%');
            })
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
        $validated = $request->validate([
            'form_id' => 'nullable|exists:deposit_forms,id',
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100|exists:servers,nama_server',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
        ]);

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

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|exists:suppliers,nama_supplier',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:0',
            'bank' => 'required|string|max:100',
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
            'reply_penambahan' => 'required|string',
        ]);

        $item = Deposit::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (($item->status ?? 'pending') !== 'approved') {
            return redirect()->route('deposit.request.index')->with('error', 'Request belum approved oleh admin');
        }

        $item->reply_penambahan = $validated['reply_penambahan'];
        $item->save();

        return redirect()->route('deposit.request.index')->with('success', 'Reply Penambahan berhasil diupdate');
    }
}
