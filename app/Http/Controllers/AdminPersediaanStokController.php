<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminPersediaanStokController extends Controller
{
    public function index(Request $request)
    {
        $query = PersediaanStok::with('user')->orderByDesc('created_at');

        if ($request->filled('q')) {
            $query->where('owner_name', 'like', '%'.$request->q.'%');
        }

        $list = $query->paginate(20);

        return view('admin.persediaan.index', compact('list'));
    }

    public function show($id)
    {
        $item = PersediaanStok::findOrFail($id);
        return view('admin.persediaan.show', compact('item'));
    }

    public function viewFile($id, $field)
    {
        $item = PersediaanStok::findOrFail($id);
        $path = null;
        if ($field === 'transfer') {
            $path = $item->transfer_proof_path;
        } elseif ($field === 'goods') {
            $path = $item->goods_photo_path;
        } else {
            $path = $item->invoice_path;
        }

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return Storage::disk('public')->response($path);
    }

    public function downloadInvoicePdf($id)
    {
        $item = PersediaanStok::findOrFail($id);

        // if raw invoice file is a PDF, download it directly
        if ($item->invoice_path && Storage::disk('public')->exists($item->invoice_path)) {
            $mime = Storage::disk('public')->mimeType($item->invoice_path);
            if ($mime === 'application/pdf') {
                return Storage::disk('public')->download($item->invoice_path, 'invoice_'.$item->id.'.pdf');
            }
        }

        // render HTML invoice from text and items
        $html = view('admin.persediaan.invoice_pdf', compact('item'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice_'.$item->id.'.pdf"');
    }

    public function updateDetails(Request $request, $id)
    {
        $item = PersediaanStok::findOrFail($id);

        $validated = $request->validate([
            'cicilan' => 'required|string|in:Tanpa Cicilan,Cicilan,Cicilan 1,Cicilan 2,Cicilan 3',
            'receive_date' => 'nullable|date',
            'transfer_proof' => 'nullable|image|max:5120',
            'transfer_proof_base64' => 'nullable|string',
            'status' => 'nullable|string|in:pending,approved,rejected,selesai',
        ]);

        $item->cicilan = $validated['cicilan'];
        if (!empty($validated['receive_date'])) {
            $item->receive_date = $validated['receive_date'];
        }

        // handle upload transfer proof by Admin
        if ($request->hasFile('transfer_proof')) {
            $item->transfer_proof_path = $request->file('transfer_proof')->store('persediaan', 'public');
            // If transfer proof is provided, auto approve / ACC if currently pending
            if ($item->status === 'pending') {
                $item->status = 'approved';
            }
        } else {
            $base64 = $request->input('transfer_proof_base64');
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
                            $filename = 'persediaan/transfer_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                            Storage::disk('public')->put($filename, $fileData);
                            $item->transfer_proof_path = $filename;
                            if ($item->status === 'pending') {
                                $item->status = 'approved';
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Admin base64 transfer proof save error: ' . $e->getMessage());
                }
            }
        }

        if (!empty($validated['status'])) {
            $item->status = $validated['status'];
        }

        $item->save();

        return redirect()->back()->with('success', 'Detail PO #' . $item->id . ' berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $item = PersediaanStok::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,selesai',
            'transfer_proof' => 'nullable|image|max:5120',
            'transfer_proof_base64' => 'nullable|string',
        ]);

        // handle upload transfer proof if provided alongside status update
        if ($request->hasFile('transfer_proof')) {
            $item->transfer_proof_path = $request->file('transfer_proof')->store('persediaan', 'public');
        } else {
            $base64 = $request->input('transfer_proof_base64');
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
                            $filename = 'persediaan/transfer_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                            Storage::disk('public')->put($filename, $fileData);
                            $item->transfer_proof_path = $filename;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Admin base64 transfer proof status update error: ' . $e->getMessage());
                }
            }
        }

        $item->status = $validated['status'];
        $item->save();

        $statusLabel = $item->status === 'approved' ? 'disetujui (ACC) dan diteruskan ke Dashboard PO' : ($item->status === 'rejected' ? 'ditolak' : $item->status);

        return redirect()->back()->with('success', 'Status Request PO #' . $item->id . ' berhasil diubah menjadi ' . $statusLabel . '.');
    }
}
