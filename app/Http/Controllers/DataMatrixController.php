<?php

namespace App\Http\Controllers;

use App\Imports\TagNomorPascaBayarImport;
use App\Imports\TagPlnInternetImport;
use App\Imports\TagLainnyaImport;
use App\Models\TagLainnya;
use App\Models\TagLainnyaPeriod;
use App\Models\TagNomorPascaBayarPeriod;
use App\Models\TagPlnInternet;
use App\Models\TagPlnInternetPeriod;
use App\Models\TagNomorPascaBayar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataMatrixController extends Controller
{
    public function tagNomorPascaBayar()
    {
        return view('admin.data-matrix.tag-nomor-pasca-bayar', [
            'title' => 'Tag Nomor Pasca Bayar',
            'menuDataMatrixTagPascaBayar' => 'active',
            'items' => TagNomorPascaBayar::with('periods')->orderBy('id')->paginate(20),
        ]);
    }

    public function storeTagNomorPascaBayar(Request $request)
    {
        $validated = $this->validatePascaBayar($request);

        DB::transaction(function () use ($validated) {
            TagNomorPascaBayar::create($validated);
        });

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

    public function storePeriodTagNomorPascaBayar(Request $request, int $id)
    {
        $validated = $this->validatePeriodWithBank($request);
        $item = TagNomorPascaBayar::findOrFail($id);

        TagNomorPascaBayarPeriod::updateOrCreate(
            [
                'tag_nomor_pasca_bayar_id' => $item->id,
                'periode_bulan' => $validated['periode_bulan'],
                'periode_tahun' => $validated['periode_tahun'],
            ],
            [
                'tagihan' => $validated['tagihan'] ?? null,
                'bank' => $validated['bank'] ?? null,
                'tanggal_payment' => $validated['tanggal_payment'] ?? null,
            ]
        );

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Periode berhasil disimpan');
    }

    public function destroyPeriodTagNomorPascaBayar(int $id, int $periodId)
    {
        $item = TagNomorPascaBayar::findOrFail($id);
        $period = $item->periods()->where('id', $periodId)->firstOrFail();
        $period->delete();

        return redirect()->route('admin.data-matrix.tag-pasca-bayar')->with('success', 'Periode berhasil dihapus');
    }

    public function tagPlnInternet()
    {
        return view('admin.data-matrix.tag-pln-internet', [
            'title' => 'Tag PLN & Internet',
            'menuDataMatrixTagPlnInternet' => 'active',
            'items' => TagPlnInternet::with('periods')->orderBy('id')->paginate(20),
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

    public function storePeriodTagPlnInternet(Request $request, int $id)
    {
        $validated = $this->validatePeriod($request);
        $item = TagPlnInternet::findOrFail($id);

        TagPlnInternetPeriod::updateOrCreate(
            [
                'tag_pln_internet_id' => $item->id,
                'periode_bulan' => $validated['periode_bulan'],
                'periode_tahun' => $validated['periode_tahun'],
            ],
            [
                'tagihan' => $validated['tagihan'] ?? null,
                'tanggal_payment' => $validated['tanggal_payment'] ?? null,
            ]
        );

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Periode berhasil disimpan');
    }

    public function destroyPeriodTagPlnInternet(int $id, int $periodId)
    {
        $item = TagPlnInternet::findOrFail($id);
        $period = $item->periods()->where('id', $periodId)->firstOrFail();
        $period->delete();

        return redirect()->route('admin.data-matrix.tag-pln-internet')->with('success', 'Periode berhasil dihapus');
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
        return view('admin.data-matrix.tag-lainnya', [
            'title' => 'Tag Lainnya',
            'menuDataMatrixTagLainnya' => 'active',
            'items' => TagLainnya::with('periods')->orderBy('id')->paginate(20),
        ]);
    }

    public function historyTagNomorPascaBayar()
    {
        return view('admin.data-matrix.riwayat-tag-nomor-pasca-bayar', [
            'title' => 'Riwayat Tagihan Nomor Pasca Bayar',
            'menuDataMatrixHistoryTagPascaBayar' => 'active',
            'items' => TagNomorPascaBayarPeriod::with('parent')
                ->orderByDesc('periode_tahun')
                ->orderByDesc('periode_bulan')
                ->orderByDesc('id')
                ->paginate(30),
        ]);
    }

    public function historyTagPlnInternet()
    {
        return view('admin.data-matrix.riwayat-tag-pln-internet', [
            'title' => 'Riwayat Tagihan PLN & Internet',
            'menuDataMatrixHistoryTagPlnInternet' => 'active',
            'items' => TagPlnInternetPeriod::with('parent')
                ->orderByDesc('periode_tahun')
                ->orderByDesc('periode_bulan')
                ->orderByDesc('id')
                ->paginate(30),
        ]);
    }

    public function historyTagLainnya()
    {
        return view('admin.data-matrix.riwayat-tag-lainnya', [
            'title' => 'Tagihan Lainnya',
            'menuDataMatrixHistoryTagLainnya' => 'active',
            'items' => TagLainnyaPeriod::with('parent')
                ->orderByDesc('periode_tahun')
                ->orderByDesc('periode_bulan')
                ->orderByDesc('id')
                ->paginate(30),
        ]);
    }

    public function importTagLainnya(Request $request)
    {
        $validated = $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new TagLainnyaImport(), $validated['file_excel']);

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Import Excel berhasil. Data langsung terisi/terupdate.');
    }

    public function storeTagLainnya(Request $request)
    {
        $validated = $this->validateTagLainnya($request);
        TagLainnya::create($validated);

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Data tag lainnya berhasil ditambahkan');
    }

    public function updateTagLainnya(Request $request, int $id)
    {
        $validated = $this->validateTagLainnya($request);
        $item = TagLainnya::findOrFail($id);
        $item->update($validated);

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Data tag lainnya berhasil diupdate');
    }

    public function destroyTagLainnya(int $id)
    {
        $item = TagLainnya::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Data tag lainnya berhasil dihapus');
    }

    public function storePeriodTagLainnya(Request $request, int $id)
    {
        $validated = $this->validatePeriodWithTime($request);
        $item = TagLainnya::findOrFail($id);

        TagLainnyaPeriod::updateOrCreate(
            [
                'tag_lainnya_id' => $item->id,
                'periode_bulan' => $validated['periode_bulan'],
                'periode_tahun' => $validated['periode_tahun'],
            ],
            [
                'tagihan' => $validated['tagihan'] ?? null,
                'tanggal_payment' => $validated['tanggal_payment'] ?? null,
            ]
        );

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Periode berhasil disimpan');
    }

    public function destroyPeriodTagLainnya(int $id, int $periodId)
    {
        $item = TagLainnya::findOrFail($id);
        $period = $item->periods()->where('id', $periodId)->firstOrFail();
        $period->delete();

        return redirect()->route('admin.data-matrix.tag-lainnya')->with('success', 'Periode berhasil dihapus');
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
        ]);
    }

    private function validatePeriod(Request $request): array
    {
        return $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'tagihan' => 'nullable|numeric|min:0',
            'tanggal_payment' => 'nullable|date',
        ]);
    }

    private function validatePeriodWithBank(Request $request): array
    {
        return $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'tagihan' => 'nullable|numeric|min:0',
            'bank' => 'nullable|string|max:255',
            'tanggal_payment' => 'nullable|date',
        ]);
    }

    private function validatePeriodWithTime(Request $request): array
    {
        return $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'tagihan' => 'nullable|numeric|min:0',
            'tanggal_payment' => 'nullable|date',
        ]);
    }

    private function validateTagLainnya(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'no_rekening_va' => 'required|string|max:100',
            'jumlah' => 'nullable|numeric|min:0',
            'bank' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);
    }
}
