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
        $path = $field === 'transfer' ? $item->transfer_proof_path : $item->invoice_path;
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
}
