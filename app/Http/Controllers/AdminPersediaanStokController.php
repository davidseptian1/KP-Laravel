<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersediaanStok;
use Illuminate\Support\Facades\Storage;

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
}
