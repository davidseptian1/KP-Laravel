<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Minusan;

class RekapController extends Controller
{
    // ===============================
    // HALAMAN REPORT KHUSUS
    // ===============================
    public function reportKhusus()
    {
        $data = Minusan::orderBy('tanggal', 'desc')->get();

        return view('admin.report-khusus.index', [
            'title' => 'Report Khusus',
            'data'  => $data,
            'menuAdminReportKhusus' => 'active',
            'jumlahReportKhusus' => $data->count(),
        ]);
    }

    // ===============================
    // SIMPAN DATA
    // ===============================
    public function storeReportKhusus(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama'    => 'required|string|max:100',
            'produk'  => 'required|string|max:100',
            'total'   => 'required|numeric',
            'server'  => 'required|string|max:50',
            'note'    => 'nullable|string',
        ]);

        Minusan::create($request->all());

        return redirect()
            ->route('admin.report.khusus.index')
            ->with('success', 'Report khusus berhasil ditambahkan');
    }

    // ===============================
    // HAPUS DATA
    // ===============================
    public function destroyReportKhusus($id)
    {
        Minusan::findOrFail($id)->delete();

        return redirect()
            ->route('admin.report.khusus.index')
            ->with('success', 'Report khusus berhasil dihapus');
    }
}
