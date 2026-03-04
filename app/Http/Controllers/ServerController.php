<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    private function cardColorOptions(): array
    {
        return [
            'primary' => 'Biru',
            'success' => 'Hijau',
            'info' => 'Biru Muda (Cyan)',
            'warning' => 'Kuning',
            'danger' => 'Merah',
            'secondary' => 'Abu-abu'
        ];
    }

    public function index()
    {
        return view('admin.server.index', [
            'title' => 'Server Manajemen',
            'menuAdminServer' => 'active',
            'items' => Server::orderByDesc('created_at')->get(),
            'cardColorOptions' => $this->cardColorOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_server' => 'required|string|max:255|unique:servers,nama_server',
            'card_color' => 'required|in:' . implode(',', array_keys($this->cardColorOptions())),
        ]);

        Server::create($validated);

        return redirect()->route('admin.server.index')->with('success', 'Server berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_server' => 'required|string|max:255|unique:servers,nama_server,' . $id,
            'card_color' => 'required|in:' . implode(',', array_keys($this->cardColorOptions())),
        ]);

        $item = Server::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.server.index')->with('success', 'Server berhasil diupdate');
    }

    public function destroy(int $id)
    {
        $item = Server::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.server.index')->with('success', 'Server berhasil dihapus');
    }
}
