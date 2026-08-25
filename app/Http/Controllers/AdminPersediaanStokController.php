<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
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
            'paid_at' => 'nullable|date',
            'transfer_proof' => 'nullable|image|max:5120',
            'transfer_proof_base64' => 'nullable|string',
            'nominal' => 'nullable|numeric',
            'note' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,approved,rejected,selesai',
        ]);

        $item->cicilan = $validated['cicilan'];
        if (!empty($validated['receive_date'])) {
            $item->receive_date = $validated['receive_date'];
        }

        $proofPath = null;
        // handle upload transfer proof by Admin
        if ($request->hasFile('transfer_proof')) {
            $proofPath = $request->file('transfer_proof')->store('persediaan', 'public');
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
                            $proofPath = $filename;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error('Admin base64 transfer proof save error: ' . $e->getMessage());
                }
            }
        }

        if ($proofPath) {
            $item->transfer_proof_path = $proofPath;

            // Save to installment_payments array with exact timestamp & stage
            $payments = $item->installment_payments ?? [];
            $paidAt = !empty($validated['paid_at']) ? Carbon::parse($validated['paid_at'])->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
            
            $paymentRecord = [
                'stage' => $validated['cicilan'],
                'paid_at' => $paidAt,
                'proof_path' => $proofPath,
                'nominal' => $validated['nominal'] ?? null,
                'note' => $validated['note'] ?? null,
            ];

            // Update existing stage if present, or append
            $updated = false;
            foreach ($payments as $key => $p) {
                if (($p['stage'] ?? '') === $validated['cicilan'] && $validated['cicilan'] !== 'Tanpa Cicilan') {
                    $payments[$key] = $paymentRecord;
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $payments[] = $paymentRecord;
            }

            $item->installment_payments = $payments;

            // Auto approve if currently pending
            if ($item->status === 'pending') {
                $item->status = 'approved';
            }
        }

        if (!empty($validated['status'])) {
            $item->status = $validated['status'];
        }

        $item->save();

        return redirect()->back()->with('success', 'Detail pembayaran ' . $validated['cicilan'] . ' untuk PO #' . $item->id . ' berhasil disimpan!');
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

    public function rekapBulanan(Request $request)
    {
        $today = Carbon::now();

        // Periode default: 23 bulan ini s/d 24 bulan depan
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            if ($today->day >= 23) {
                $startDate = $today->copy()->day(23)->startOfDay();
                $endDate = $today->copy()->addMonth()->day(24)->endOfDay();
            } else {
                $startDate = $today->copy()->subMonth()->day(23)->startOfDay();
                $endDate = $today->copy()->day(24)->endOfDay();
            }
        }

        $query = PersediaanStok::with('user')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('po_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->whereNull('po_date')
                         ->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('company_name', 'like', '%' . $request->supplier . '%');
        }

        $list = $query->orderByDesc('po_date')->orderByDesc('id')->get();

        // Hitung statistik
        $totalPO = $list->count();
        $totalNominal = $list->sum('total_amount');
        $totalApproved = $list->where('status', 'approved')->count();
        $totalSelesai = $list->where('status', 'selesai')->count();
        $totalPending = $list->where('status', 'pending')->count();
        $totalRejected = $list->where('status', 'rejected')->count();

        // Grouping per Supplier
        $supplierSummary = $list->groupBy(function ($item) {
            return $item->company_name ?: 'Tanpa Supplier';
        })->map(function ($items, $name) {
            return [
                'name' => $name,
                'count' => $items->count(),
                'total_amount' => $items->sum('total_amount'),
            ];
        })->sortByDesc('total_amount');

        // Grouping per Server (Division)
        $serverSummary = $list->groupBy(function ($item) {
            return $item->division ?: 'Tanpa Server';
        })->map(function ($items, $name) {
            return [
                'name' => $name,
                'count' => $items->count(),
                'total_amount' => $items->sum('total_amount'),
            ];
        })->sortByDesc('total_amount');

        return view('admin.persediaan.rekap', compact(
            'list', 'startDate', 'endDate',
            'totalPO', 'totalNominal', 'totalApproved', 'totalSelesai',
            'totalPending', 'totalRejected', 'supplierSummary', 'serverSummary'
        ));
    }

    public function rekapBulananPdf(Request $request)
    {
        $today = Carbon::now();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            if ($today->day >= 23) {
                $startDate = $today->copy()->day(23)->startOfDay();
                $endDate = $today->copy()->addMonth()->day(24)->endOfDay();
            } else {
                $startDate = $today->copy()->subMonth()->day(23)->startOfDay();
                $endDate = $today->copy()->day(24)->endOfDay();
            }
        }

        $query = PersediaanStok::with('user')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('po_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->whereNull('po_date')
                         ->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier')) {
            $query->where('company_name', 'like', '%' . $request->supplier . '%');
        }

        $list = $query->orderByDesc('po_date')->orderByDesc('id')->get();

        $totalPO = $list->count();
        $totalNominal = $list->sum('total_amount');
        $totalApproved = $list->where('status', 'approved')->count();
        $totalSelesai = $list->where('status', 'selesai')->count();
        $totalPending = $list->where('status', 'pending')->count();
        $totalRejected = $list->where('status', 'rejected')->count();

        $html = view('admin.persediaan.rekap_pdf', compact(
            'list', 'startDate', 'endDate',
            'totalPO', 'totalNominal', 'totalApproved', 'totalSelesai',
            'totalPending', 'totalRejected'
        ))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="rekap_po_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.pdf"');
    }
}
