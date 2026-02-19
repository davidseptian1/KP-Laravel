<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('admin.supplier.index', [
            'title' => 'Supplier Manajemen',
            'menuAdminSupplier' => 'active',
            'items' => Supplier::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|unique:suppliers,nama_supplier',
        ]);

        Supplier::create($validated);

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|unique:suppliers,nama_supplier,' . $id,
        ]);

        $item = Supplier::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil diupdate');
    }

    public function destroy(int $id)
    {
        $item = Supplier::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil dihapus');
    }
}
