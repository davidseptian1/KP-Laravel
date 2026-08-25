<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use App\Models\Bank;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Server;

class PersediaanStokController extends Controller
{
    public function create()
    {
        $banks = [];
        if (class_exists(Bank::class)) {
            $banks = Bank::orderBy('nama_bank')->get();
        }

        $servers = [];
        if (class_exists(Server::class)) {
            $servers = Server::orderBy('nama_server')->get();
        }

        // load recent records for the current user so they appear in the table
        $records = PersediaanStok::where('user_id', Auth::id())->orderByDesc('created_at')->get();

        // show index-like page with modal form
        return view('persediaan.index', compact('banks', 'servers', 'records'));
    }

    public function viewFile($id, $field)
    {
        $item = PersediaanStok::findOrFail($id);
        $path = null;
        if ($field === 'transfer') {
            $path = $item->transfer_proof_path;
        } elseif ($field === 'goods') {
            $path = $item->goods_photo_path;
        } elseif (str_starts_with($field, 'cicilan_')) {
            $idx = (int) str_replace('cicilan_', '', $field);
            $payments = $item->installment_payments ?? [];
            $path = $payments[$idx]['proof_path'] ?? null;
        } else {
            $path = $item->invoice_path;
        }

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return Storage::disk('public')->response($path);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'payment_method' => 'required|string|in:bank,va',
            'bank_id' => 'nullable|integer',
            'cicilan' => 'required|string|in:Tanpa Cicilan,Cicilan,Cicilan 1,Cicilan 2,Cicilan 3',
            'po_date' => 'required|date',
            'owner_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'receive_date' => 'nullable|date',
            'items_json' => 'nullable|string',
            'on_behalf' => 'nullable|string|max:255',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'invoice_file_base64' => 'nullable|string',
            'invoice_text' => 'nullable|string'
        ]);

        $items = json_decode($data['items_json'] ?? '[]', true) ?: [];
        $total = 0;
        foreach ($items as $i) {
            $subtotal = floatval($i['qty'] ?? 0) * floatval($i['price'] ?? 0);
            $total += $subtotal;
        }

        $record = new PersediaanStok();
        $record->user_id = Auth::id();
        $record->company_name = $data['company_name'];
        $record->division = $data['division'];
        $record->payment_method = $data['payment_method'];
        $record->cicilan = $data['cicilan'];
        $record->po_date = $data['po_date'];
        $record->owner_name = !empty($data['owner_name']) ? $data['owner_name'] : $data['company_name'];
        $record->bank_id = $data['bank_id'] ?? null;
        $record->account_number = $data['account_number'] ?? null;
        $record->account_name = $data['account_name'] ?? null;
        $record->purchase_date = $data['purchase_date'] ?? $data['po_date'] ?? null;
        $record->receive_date = $data['receive_date'] ?? null;
        $record->items = $items;
        $record->total_amount = $total;
        $record->on_behalf = $data['on_behalf'] ?? null;



        if ($request->hasFile('invoice_file')) {
            $record->invoice_path = $request->file('invoice_file')->store('persediaan', 'public');
        } else {
            $base64Inv = $request->input('invoice_file_base64') ?: ($data['invoice_file_base64'] ?? null);
            if (!empty($base64Inv)) {
                try {
                    if (str_contains($base64Inv, 'base64,')) {
                        $parts = explode('base64,', $base64Inv);
                        $meta = $parts[0];
                        $rawBase64 = end($parts);
                        
                        $ext = str_contains($meta, 'pdf') ? 'pdf' : 'png';
                        if (str_contains($meta, 'jpeg') || str_contains($meta, 'jpg')) {
                            $ext = 'jpg';
                        } elseif (str_contains($meta, 'webp')) {
                            $ext = 'webp';
                        }
                        
                        $fileData = base64_decode(str_replace(' ', '+', trim($rawBase64)));
                        if ($fileData !== false && strlen($fileData) > 0) {
                            $filename = 'persediaan/invoice_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                            Storage::disk('public')->put($filename, $fileData);
                            $record->invoice_path = $filename;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Base64 invoice file save error: ' . $e->getMessage());
                }
            }
        }

        $record->invoice_text = $data['invoice_text'] ?? null;
        $record->save();

        return redirect()->route('persediaan.create')->with('success', 'Permintaan persediaan dikirim.');
    }
}
