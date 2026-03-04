<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    private function cardColorOptions(): array
    {
        return ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
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
            'card_color' => 'required|in:' . implode(',', $this->cardColorOptions()),
        ]);

        Server::create($validated);

        return redirect()->route('admin.server.index')->with('success', 'Server berhasil ditambahkan');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_server' => 'required|string|max:255|unique:servers,nama_server,' . $id,
            'card_color' => 'required|in:' . implode(',', $this->cardColorOptions()),
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
