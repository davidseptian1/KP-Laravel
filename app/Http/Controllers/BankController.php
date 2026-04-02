<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        return view('admin.bank.index', [
            'title' => 'Bank Manajemen',
            'menuAdminBank' => 'active',
            'items' => Bank::orderByDesc('created_at')->get(),
            'users' => User::orderBy('email')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bank' => 'required|string|max:255|unique:banks,nama_bank',
            'user_email' => 'nullable',
        ]);

        // allow special value 'ALL' to mean assigned to all users
        if (!empty($validated['user_email']) && $validated['user_email'] !== 'ALL') {
            $request->validate(['user_email' => 'email|exists:users,email']);
        }

        Bank::create($validated);

        return redirect()->route('admin.bank.index')->with('success', 'Bank berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_bank' => 'required|string|max:255|unique:banks,nama_bank,' . $id,
            'user_email' => 'nullable',
        ]);

        if (!empty($validated['user_email']) && $validated['user_email'] !== 'ALL') {
            $request->validate(['user_email' => 'email|exists:users,email']);
        }

        $item = Bank::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.bank.index')->with('success', 'Bank berhasil diupdate');
    }

    public function destroy(int $id)
    {
        $item = Bank::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.bank.index')->with('success', 'Bank berhasil dihapus');
    }
}
