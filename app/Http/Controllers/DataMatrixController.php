<?php

namespace App\Http\Controllers;

use App\Models\TagNomorPascaBayar;
use Illuminate\Http\Request;

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

    public function tagPlnInternet()
    {
        return view('admin.data-matrix.index', [
            'title' => 'Tag PLN & Internet',
            'menuDataMatrixTagPlnInternet' => 'active',
            'pageTitle' => 'Tag PLN & Internet',
            'pageDescription' => 'Halaman Data Matrix untuk kebutuhan tag PLN dan Internet.',
        ]);
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
}
