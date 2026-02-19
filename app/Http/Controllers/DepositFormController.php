<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositForm;
use App\Models\NotificationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositFormController extends Controller
{
    public function index()
    {
        $activeForms = DepositForm::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('created_at')
            ->get();

        $items = Deposit::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('staff.deposit.index', [
            'title' => 'Request Deposit',
            'menuDepositRequest' => 'active',
            'activeForms' => $activeForms,
            'items' => $items,
        ]);
    }

    public function storeFromRequestPage(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'nullable|exists:deposit_forms,id',
            'nama_supplier' => 'required|string|max:255',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:1',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'jam' => 'required|date_format:H:i',
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
            'jam' => $validated['jam'],
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
            'nama_supplier' => 'required|string|max:255',
            'jenis_transaksi' => 'required|in:deposit,hutang',
            'nominal' => 'required|numeric|min:0',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100',
            'no_rek' => 'required|regex:/^[0-9]+$/|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_tiket' => 'nullable|string',
            'jam' => 'required|date_format:H:i',
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
            'jam' => $validated['jam'],
        ]);

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
            "Jam          : {$deposit->jam}";

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
