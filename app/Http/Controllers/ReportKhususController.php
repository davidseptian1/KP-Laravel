<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportKhusus;

class ReportKhususController extends Controller
{
    // =====================
    // INDEX
    // =====================
    public function index(Request $request)
{
    $query = ReportKhusus::query();

    if ($request->bulan) {
        $query->whereMonth('tanggal', $request->bulan);
    }

    if ($request->tahun) {
        $query->whereYear('tanggal', $request->tahun);
    }

    $reports = $query->orderBy('tanggal', 'desc')->get();

    return view('admin.report-khusus.index', [
        'title'   => 'Report Khusus',
        'reports' => $reports
    ]);
}


    // =====================
    // STORE
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'        => 'required|date',
            'nama'           => 'required|string|max:100',
            'produk'         => 'required|string|max:100',
            'nomor_tujuan'   => 'required|string|max:30',
            'supplier'       => 'required|string|max:100',
            'total'          => 'required|numeric',
            'server'         => 'required|string|max:50',
            'note'           => 'nullable|string',
        ]);

        ReportKhusus::create($request->all());

        return redirect()
            ->route('admin.report.khusus.index')
            ->with('success', 'Report khusus berhasil ditambahkan');
    }

    // =====================
    // UPDATE
    // =====================
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'        => 'required|date',
            'nama'           => 'required|string|max:100',
            'produk'         => 'required|string|max:100',
            'nomor_tujuan'   => 'required|string|max:30',
            'supplier'       => 'required|string|max:100',
            'total'          => 'required|numeric',
            'server'         => 'required|string|max:50',
            'note'           => 'nullable|string',
        ]);

        ReportKhusus::findOrFail($id)->update($request->all());

        return redirect()
            ->route('admin.report.khusus.index')
            ->with('success', 'Report khusus berhasil diupdate');
    }

    // =====================
    // DELETE
    // =====================
    public function destroy($id)
    {
        ReportKhusus::findOrFail($id)->delete();

        return redirect()
            ->route('admin.report.khusus.index')
            ->with('success', 'Report khusus berhasil dihapus');
    }
}
