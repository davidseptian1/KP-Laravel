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
            // banks table uses column `nama_bank`
            $banks = Bank::orderBy('nama_bank')->get();
        }

        // load recent records for the current user so they appear in the table
        $records = PersediaanStok::where('user_id', Auth::id())->orderByDesc('created_at')->get();

        // show index-like page with modal form (match Request Deposit UI)
        return view('persediaan.index', compact('banks', 'records'));
    }

    public function viewFile($id, $field)
    {
        $item = PersediaanStok::findOrFail($id);
        $path = $field === 'transfer' ? $item->transfer_proof_path : $item->invoice_path;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return Storage::disk('public')->response($path);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'division' => 'required|string|in:server,gudang',
            'payment_method' => 'required|string|in:bank,va',
            'bank_id' => 'nullable|integer',
            'cicilan' => 'required|string|in:Cicilan,Tanpa Cicilan',
            'po_date' => 'required|date',
            'owner_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'receive_date' => 'nullable|date',
            'items_json' => 'nullable|string',
            'on_behalf' => 'nullable|string|max:255',
            'transfer_proof' => 'nullable|image|max:5120',
            'transfer_proof_base64' => 'nullable|string',
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

        // handle uploaded file
        if ($request->hasFile('transfer_proof')) {
            $record->transfer_proof_path = $request->file('transfer_proof')->store('persediaan', 'public');
        } elseif (!empty($data['transfer_proof_base64'])) {
            // save base64 image to storage
            try {
                $fileData = $data['transfer_proof_base64'];
                if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-]+);base64,/', $fileData, $type)) {
                    $fileData = substr($fileData, strpos($fileData, ',') + 1);
                    $fileData = base64_decode($fileData);
                    $mime = $type[1];
                    $ext = explode('/', $mime)[1] ?? 'png';
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $filename = 'persediaan/transfer_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    Storage::disk('public')->put($filename, $fileData);
                    $record->transfer_proof_path = $filename;
                }
            } catch (\Exception $e) {
                // ignore saving base64 on failure
            }
        }

        if ($request->hasFile('invoice_file')) {
            $record->invoice_path = $request->file('invoice_file')->store('persediaan', 'public');
        } elseif (!empty($data['invoice_file_base64'])) {
            try {
                $fileData = $data['invoice_file_base64'];
                if (preg_match('/^data:(application\/pdf|image\/[a-zA-Z0-9\+\-]+);base64,/', $fileData, $type)) {
                    $fileData = substr($fileData, strpos($fileData, ',') + 1);
                    $fileData = base64_decode($fileData);
                    $mime = $type[1];
                    $ext = strpos($mime, 'pdf') !== false ? 'pdf' : (explode('/', $mime)[1] ?? 'png');
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $filename = 'persediaan/invoice_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    Storage::disk('public')->put($filename, $fileData);
                    $record->invoice_path = $filename;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $record->invoice_text = $data['invoice_text'] ?? null;
        $record->save();

        return redirect()->route('persediaan.create')->with('success', 'Permintaan persediaan dikirim.');
    }
}
