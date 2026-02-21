<?php

namespace App\Http\Controllers;

use App\Imports\TagNomorPascaBayarImport;
use App\Imports\TagPlnInternetImport;
use App\Models\TagPlnInternet;
use App\Models\TagNomorPascaBayar;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DataMatrixController extends Controller
{
    public function tagNomorPascaBayar()
    {
        return view('admin.data-matrix.tag-nomor-pasca-bayar', [
            'title' => 'Tag Nomor Pasca Bayar',
            'menuDataMatrixTagPascaBayar' => 'active',
            'items' => TagNomorPascaBayar::orderBy('id')->paginate(20),
        ]);
    }

    public function storeTagNomorPascaBayar(Request $request)
    {
        $validated = $this->validatePascaBayar($request);
        TagNomorPascaBayar::create($validated);

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Data nomor pasca bayar berhasil ditambahkan');
    }

    public function updateTagNomorPascaBayar(Request $request, int $id)
    {
        $validated = $this->validatePascaBayar($request);
        $item = TagNomorPascaBayar::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Data nomor pasca bayar berhasil diupdate');
    }

    public function destroyTagNomorPascaBayar(int $id)
    {
        $item = TagNomorPascaBayar::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Data nomor pasca bayar berhasil dihapus');
    }

    public function importTagNomorPascaBayar(Request $request)
    {
        $validated = $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new TagNomorPascaBayarImport(), $validated['file_excel']);

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Import Excel berhasil. Data langsung terisi/terupdate.');
    }

    public function tagPlnInternet()
    {
        return view('admin.data-matrix.tag-pln-internet', [
            'title' => 'Tag PLN & Internet',
            'menuDataMatrixTagPlnInternet' => 'active',
            'items' => TagPlnInternet::orderBy('id')->paginate(20),
        ]);
    }

    public function importTagPlnInternet(Request $request)
    {
        $validated = $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new TagPlnInternetImport(), $validated['file_excel']);

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Import Excel berhasil. Data langsung terisi/terupdate.');
    }

    public function storeTagPlnInternet(Request $request)
    {
        $validated = $this->validateTagPlnInternet($request);
        TagPlnInternet::create($validated);

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Data tag PLN & Internet berhasil ditambahkan');
    }

    public function updateTagPlnInternet(Request $request, int $id)
    {
        $validated = $this->validateTagPlnInternet($request);
        $item = TagPlnInternet::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Data tag PLN & Internet berhasil diupdate');
    }

    public function destroyTagPlnInternet(int $id)
    {
        $item = TagPlnInternet::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Data tag PLN & Internet berhasil dihapus');
    }

    public function tagLainnya()
    {
        return view('admin.data-matrix.index', [
            'title' => 'Tag Lainnya',
            'menuDataMatrixTagLainnya' => 'active',
            'pageTitle' => 'Tag Lainnya',
            'pageDescription' => 'Halaman Data Matrix untuk kebutuhan tag lainnya.',
        ]);
    }

    private function validatePascaBayar(Request $request): array
    {
        return $request->validate([
            'nomor' => 'required|string|max:30',
            'atas_nama' => 'required|string|max:255',
            'chip' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
            'status' => 'required|string|max:100',
            'periode_des_2025_tagihan' => 'nullable|numeric|min:0',
            'periode_des_2025_bank' => 'nullable|string|max:255',
            'periode_feb_2026_tanggal_payment' => 'nullable|date',
            'periode_feb_2026_tagihan' => 'nullable|numeric|min:0',
        ]);
    }

    private function validateTagPlnInternet(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_pln_internet' => 'required|string|max:50',
            'atas_nama' => 'required|string|max:255',
            'bank' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'periode_januari_2026_tagihan' => 'nullable|numeric|min:0',
            'periode_januari_2026_tanggal_payment' => 'nullable|date',
            'periode_februari_2026_tagihan' => 'nullable|numeric|min:0',
            'periode_februari_2026_tanggal_payment' => 'nullable|date',
        ]);
    }
}
