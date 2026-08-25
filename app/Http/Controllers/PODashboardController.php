<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PODashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending_confirmation'); // pending_confirmation or completed

        $query = PersediaanStok::with('user')->orderByDesc('created_at');

        if ($tab === 'completed') {
            $query->where('status', 'selesai');
        } else {
            // Pending confirmation on PO dashboard = requests approved (ACC) by admin but not yet completed by PO
            $query->where('status', 'approved');
        }

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('company_name', 'like', '%'.$request->q.'%')
                  ->orWhere('owner_name', 'like', '%'.$request->q.'%')
                  ->orWhere('division', 'like', '%'.$request->q.'%');
            });
        }

        $list = $query->paginate(20)->withQueryString();

        $pendingCount = PersediaanStok::where('status', 'approved')->count();
        $completedCount = PersediaanStok::where('status', 'selesai')->count();

        return view('po.dashboard', compact('list', 'tab', 'pendingCount', 'completedCount'));
    }

    public function confirmReceive(Request $request, $id)
    {
        $item = PersediaanStok::findOrFail($id);

        $validated = $request->validate([
            'receive_date' => 'nullable|date',
            'goods_photo' => 'nullable|image|max:5120',
            'goods_photo_base64' => 'nullable|string',
        ]);

        $item->receive_date = !empty($validated['receive_date']) ? $validated['receive_date'] : now();

        // Handle uploaded goods photo file or base64 (Ctrl+V)
        if ($request->hasFile('goods_photo')) {
            $item->goods_photo_path = $request->file('goods_photo')->store('persediaan', 'public');
        } else {
            $base64 = $request->input('goods_photo_base64');
            if (!empty($base64)) {
                try {
                    if (str_contains($base64, 'base64,')) {
                        $parts = explode('base64,', $base64);
                        $meta = $parts[0];
                        $rawBase64 = end($parts);
                        
                        $ext = 'png';
                        if (str_contains($meta, 'jpeg') || str_contains($meta, 'jpg')) {
                            $ext = 'jpg';
                        } elseif (str_contains($meta, 'webp')) {
                            $ext = 'webp';
                        }
                        
                        $fileData = base64_decode(str_replace(' ', '+', trim($rawBase64)));
                        if ($fileData !== false && strlen($fileData) > 0) {
                            $filename = 'persediaan/goods_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                            Storage::disk('public')->put($filename, $fileData);
                            $item->goods_photo_path = $filename;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Base64 goods photo save error: ' . $e->getMessage());
                }
            }
        }

        $item->status = 'selesai';
        $item->save();

        return redirect()->back()->with('success', 'Penerimaan barang untuk Request PO #' . $item->id . ' telah dikonfirmasi!');
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
}
