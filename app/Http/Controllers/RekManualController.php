<?php

namespace App\Http\Controllers;

use App\Models\RekManual;
use Illuminate\Http\Request;

class RekManualController extends Controller
{
    public function index()
    {
        return view('admin.rek-manual.index', [
            'title' => 'Rek Manual Manajement',
            'menuAdminRekManual' => 'active',
            'items' => RekManual::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_tujuan' => 'required|string|max:100',
            'no_rek' => 'required|string|max:100|unique:rek_manuals,no_rek',
            'nama_rekening' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        RekManual::create($validated);

        return redirect()->route('admin.rek-manual.index')->with('success', 'Data Rek Manual berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'bank_tujuan' => 'required|string|max:100',
            'no_rek' => 'required|string|max:100|unique:rek_manuals,no_rek,' . $id,
            'nama_rekening' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $item = RekManual::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.rek-manual.index')->with('success', 'Data Rek Manual berhasil diupdate');
    }

    public function destroy(int $id)
    {
        $item = RekManual::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.rek-manual.index')->with('success', 'Data Rek Manual berhasil dihapus');
    }
}
