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
            'users' => \App\Models\User::select('email', 'nama')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|unique:suppliers,nama_supplier',
            'user_email'    => 'nullable|string|email|exists:users,email',
        ]);

        Supplier::create($validated);

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255|unique:suppliers,nama_supplier,' . $id,
            'user_email'    => 'nullable|string|email|exists:users,email',
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
