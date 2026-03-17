<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use App\Models\Bank;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PersediaanStokController extends Controller
{
    public function create()
    {
        $banks = [];
        if (class_exists(Bank::class)) {
            $banks = Bank::orderBy('nama')->get();
        }

        return view('persediaan.form', compact('banks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_name' => 'required|string|max:255',
            'bank_id' => 'nullable|integer',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'receive_date' => 'nullable|date',
            'items_json' => 'required|string',
            'on_behalf' => 'nullable|string|max:255',
            'transfer_proof' => 'nullable|image|max:5120',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'invoice_text' => 'nullable|string'
        ]);

        $items = json_decode($data['items_json'], true) ?: [];
        $total = 0;
        foreach ($items as $i) {
            $subtotal = floatval($i['qty'] ?? 0) * floatval($i['price'] ?? 0);
            $total += $subtotal;
        }

        $record = new PersediaanStok();
        $record->user_id = Auth::id();
        $record->owner_name = $data['owner_name'];
        $record->bank_id = $data['bank_id'] ?? null;
        $record->account_number = $data['account_number'] ?? null;
        $record->account_name = $data['account_name'] ?? null;
        $record->purchase_date = $data['purchase_date'] ?? null;
        $record->receive_date = $data['receive_date'] ?? null;
        $record->items = $items;
        $record->total_amount = $total;
        $record->on_behalf = $data['on_behalf'] ?? null;

        if ($request->hasFile('transfer_proof')) {
            $record->transfer_proof_path = $request->file('transfer_proof')->store('persediaan', 'public');
        }

        if ($request->hasFile('invoice_file')) {
            $record->invoice_path = $request->file('invoice_file')->store('persediaan', 'public');
        }

        $record->invoice_text = $data['invoice_text'] ?? null;
        $record->save();

        return redirect()->route('deposit.request.index')->with('success', 'Permintaan persediaan dikirim.');
    }
}
