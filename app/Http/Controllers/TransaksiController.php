<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Imports\TransaksiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessTransaksiImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    /**
     * Menampilkan halaman upload CSV
     */
    public function uploadForm()
    {
        $data = [
            'title' => 'Upload Transaksi CSV',
            'menuTransaksi' => 'active',
        ];

        return view('admin.transaksi.upload', $data);
    }

    /**
     * Menghitung estimasi waktu proses upload
     */
    private function calculateEstimatedTime($totalRecords)
    {
        $timePerRecord = 0.02; // Misalnya, 0.02 detik per record
        return round($totalRecords * $timePerRecord, 2); // Dalam detik
    }

    /**
     * Proses upload dan import CSV dengan estimasi waktu
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240',
        ], [
            'csv_file.required' => 'File CSV harus dipilih',
            'csv_file.mimes' => 'File harus berformat CSV atau TXT',
            'csv_file.max' => 'Ukuran file maksimal 10MB',
        ]);

        try {
            $file = $request->file('csv_file');

            // Hitung estimasi waktu berdasarkan jumlah baris
            $totalRecords = count(file($file->getRealPath())) - 1; // Kurangi header
            $estimatedTime = $this->calculateEstimatedTime($totalRecords);

            // Store uploaded file to storage/app/imports
            $path = $file->store('imports');

            // Dispatch queued job to process the import asynchronously
            ProcessTransaksiImport::dispatch($path);

            // Include filename in session so frontend can poll for status
            return redirect()->route('transaksi.analisis')
                ->with('success', "File diterima dan akan diproses di background. Estimasi waktu: {$estimatedTime} detik.")
                ->with('import_file', basename($path));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menerima file: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan dashboard analisis transaksi
     */
    public function analisis(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $filterProduk = $request->input('filter_produk');
        $filterStatus = $request->input('filter_status');

        // Query dasar (jangan set select() di sini — biarkan agregat memilih kolomnya)
        $query = Transaksi::query();

        // Apply filters
        if ($startDate) {
            $query->whereDate('tgl_entri', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tgl_entri', '<=', $endDate);
        }
        if ($filterProduk) {
            $query->where('kode_produk', $filterProduk);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        // Data untuk analisis - gunakan database grouping
        $kecepatanAnalisis = $this->analisisKecepatanOptimized($query->clone());
        $rekomendasiProduk = $this->analisisRekomendasiProdukOptimized($query->clone());
        $performaReseller = $this->analisisResellerOptimized($query->clone());

        // === STATISTIK UMUM ===
        $counts = $query->clone()->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "Sukses" THEN 1 ELSE 0 END) as sukses,
                SUM(CASE WHEN status = "Gagal" THEN 1 ELSE 0 END) as gagal,
                SUM(laba) as total_laba,
                SUM(harga_jual) as total_jual
            ')
            ->first();

        $statistik = [
            'total_transaksi' => $counts->total ?? 0,
            'total_sukses' => $counts->sukses ?? 0,
            'total_gagal' => $counts->gagal ?? 0,
            'success_rate' => $counts->total > 0 
                ? round(($counts->sukses / $counts->total) * 100, 2)
                : 0,
            'total_pendapatan' => (int)($counts->total_jual ?? 0),
            'total_laba' => (int)($counts->total_laba ?? 0),
            'profit_margin' => ($counts->total_jual ?? 0) > 0
                ? round((($counts->total_laba ?? 0) / $counts->total_jual) * 100, 2)
                : 0,
            'rata_laba_per_transaksi' => ($counts->total ?? 0) > 0
                ? (int)(($counts->total_laba ?? 0) / $counts->total)
                : 0,
        ];

        // === DATA UNTUK FILTER ===
        $produkList = Transaksi::distinct()
            ->select('kode_produk')
            ->orderBy('kode_produk')
            ->pluck('kode_produk');
        $statusList = ['Gagal', 'Sukses', 'Proses'];

        $data = [
            'title' => 'Analisis Transaksi',
            'menuTransaksi' => 'active',
            'kecepatanAnalisis' => $kecepatanAnalisis,
            'rekomendasiProduk' => $rekomendasiProduk,
            'statistik' => $statistik,
            'performaReseller' => $performaReseller,
            'produkList' => $produkList,
            'statusList' => $statusList,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filter_produk' => $filterProduk,
                'filter_status' => $filterStatus,
            ]
        ];

        return view('admin.transaksi.analisis', $data);
    }

    /**
     * ANALISIS: Kecepatan Transaksi (Optimized dengan DB grouping)
     */
    private function analisisKecepatanOptimized($query)
    {
        $sukses = $query->clone()->where('status', 'Sukses');
        
        $stats = $sukses->clone()->selectRaw('
                AVG(durasi_detik) as rata,
                MIN(durasi_detik) as tercepat,
                MAX(durasi_detik) as terlambat,
                COUNT(*) as total
            ')->first();

        $distribusi = $sukses->clone()
            ->selectRaw('
                SUM(CASE WHEN durasi_detik < 10 THEN 1 ELSE 0 END) as cepat,
                SUM(CASE WHEN durasi_detik BETWEEN 10 AND 30 THEN 1 ELSE 0 END) as normal,
                SUM(CASE WHEN durasi_detik > 30 THEN 1 ELSE 0 END) as lambat
            ')
            ->first();

        $kecepatan_per_produk = $sukses->clone()
            ->selectRaw('
                kode_produk,
                AVG(durasi_detik) as rata_rata,
                COUNT(*) as count,
                MIN(durasi_detik) as tercepat,
                MAX(durasi_detik) as terlambat
            ')
            ->groupBy('kode_produk')
            ->orderBy('rata_rata')
            ->get()
            ->map(fn($item) => [
                'produk' => $item->kode_produk,
                'rata_rata' => round($item->rata_rata, 2),
                'count' => $item->count,
                'tercepat' => $item->tercepat,
                'terlambat' => $item->terlambat,
            ])
            ->toArray();

        $total = $stats->total ?? 0;
        
        return [
            'rata_rata_detik' => round($stats->rata ?? 0, 2),
            'tercepat' => $stats->tercepat ?? 0,
            'terlambat' => $stats->terlambat ?? 0,
            'total_berhasil' => $total,
            'distribusi_kecepatan' => [
                'cepat' => [
                    'label' => 'Cepat (< 10 detik)',
                    'count' => (int)$distribusi->cepat,
                    'persen' => $total > 0 ? round(($distribusi->cepat / $total) * 100, 2) : 0,
                ],
                'normal' => [
                    'label' => 'Normal (10-30 detik)',
                    'count' => (int)$distribusi->normal,
                    'persen' => $total > 0 ? round(($distribusi->normal / $total) * 100, 2) : 0,
                ],
                'lambat' => [
                    'label' => 'Lambat (> 30 detik)',
                    'count' => (int)$distribusi->lambat,
                    'persen' => $total > 0 ? round(($distribusi->lambat / $total) * 100, 2) : 0,
                ],
            ],
            'kecepatan_per_produk' => $kecepatan_per_produk,
        ];
    }

    /**
     * ANALISIS: Rekomendasi Produk (Optimized dengan DB grouping)
     */
    private function analisisRekomendasiProdukOptimized($query)
    {
        $rekomendasi = $query->clone()
            ->where('status', 'Sukses')
            ->selectRaw('
                kode_produk,
                COUNT(*) as jumlah_transaksi,
                SUM(harga_jual) as total_penjualan,
                SUM(laba) as total_laba,
                AVG(laba) as rata_laba,
                100 as success_rate
            ')
            ->groupBy('kode_produk')
            ->get()
            ->map(function($item) {
                $margin = $item->total_penjualan > 0 ? ($item->total_laba / $item->total_penjualan) * 100 : 0;
                $skor = ($margin * 0.4) + ($item->success_rate * 0.3) + (min($item->jumlah_transaksi / 100, 100) * 0.3);
                
                return [
                    'kode_produk' => $item->kode_produk,
                    'jumlah_transaksi' => (int)$item->jumlah_transaksi,
                    'total_penjualan' => (int)$item->total_penjualan,
                    'total_laba' => (int)$item->total_laba,
                    'rata_laba' => (int)$item->rata_laba,
                    'profit_margin' => round($margin, 2),
                    'success_rate' => round($item->success_rate, 2),
                    'skor_rekomendasi' => round($skor, 2),
                ];
            })
            ->sortByDesc('skor_rekomendasi')
            ->values()
            ->toArray();

        return $rekomendasi;
    }

    /**
     * ANALISIS: Performa Reseller (Optimized dengan DB grouping)
     */
    private function analisisResellerOptimized($query)
    {
        return $query->clone()
            ->where('status', 'Sukses')
            ->selectRaw('
                kode_reseller,
                nama_reseller,
                COUNT(*) as jumlah_transaksi,
                SUM(harga_jual) as total_penjualan,
                SUM(laba) as total_laba,
                AVG(laba) as rata_laba
            ')
            ->groupBy('kode_reseller', 'nama_reseller')
            ->orderByDesc('total_penjualan')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'kode_reseller' => $item->kode_reseller ?? 'UNKNOWN',
                'nama_reseller' => $item->nama_reseller ?? 'Tidak diketahui',
                'jumlah_transaksi' => (int)$item->jumlah_transaksi,
                'total_penjualan' => (int)$item->total_penjualan,
                'total_laba' => (int)$item->total_laba,
                'rata_laba' => (int)$item->rata_laba,
            ])
            ->toArray();
    }

    /**
     * ANALISIS: Kecepatan Transaksi (OLD - untuk backward compatibility)
     */
    private function analisisKecepatan($transaksi)
    {
        $sukses = $transaksi->where('status', 'Sukses');
        
        return [
            'rata_rata_detik' => $sukses->count() > 0 
                ? round($sukses->average('durasi_detik'), 2)
                : 0,
            'tercepat' => $sukses->count() > 0
                ? $sukses->min('durasi_detik')
                : 0,
            'terlambat' => $sukses->count() > 0
                ? $sukses->max('durasi_detik')
                : 0,
            'total_berhasil' => $sukses->count(),
            'distribusi_kecepatan' => $this->getDistribusiKecepatan($sukses),
            'kecepatan_per_produk' => $this->getKecepatanPerProduk($sukses),
        ];
    }

    /**
     * Distribusi Kecepatan (OLD - untuk backward compatibility)
     */
    private function getDistribusiKecepatan($transaksi)
    {
        $cepat = $transaksi->where('durasi_detik', '<', 10)->count();
        $normal = $transaksi->whereBetween('durasi_detik', [10, 30])->count();
        $lambat = $transaksi->where('durasi_detik', '>', 30)->count();
        $total = $transaksi->count();

        return [
            'cepat' => [
                'label' => 'Cepat (< 10 detik)',
                'count' => $cepat,
                'persen' => $total > 0 ? round(($cepat / $total) * 100, 2) : 0,
            ],
            'normal' => [
                'label' => 'Normal (10-30 detik)',
                'count' => $normal,
                'persen' => $total > 0 ? round(($normal / $total) * 100, 2) : 0,
            ],
            'lambat' => [
                'label' => 'Lambat (> 30 detik)',
                'count' => $lambat,
                'persen' => $total > 0 ? round(($lambat / $total) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Kecepatan per Produk (OLD - untuk backward compatibility)
     */
    private function getKecepatanPerProduk($transaksi)
    {
        return $transaksi->groupBy('kode_produk')
            ->map(function ($items) {
                return [
                    'produk' => $items->first()->kode_produk,
                    'rata_rata' => round($items->average('durasi_detik'), 2),
                    'count' => $items->count(),
                    'tercepat' => $items->min('durasi_detik'),
                    'terlambat' => $items->max('durasi_detik'),
                ];
            })
            ->sortBy('rata_rata')
            ->values();
    }

    /**
     * ANALISIS: Rekomendasi Produk untuk Dijual (OLD - gunakan analisisRekomendasiProdukOptimized)
     */
    private function analisisRekomendasiProduk($transaksi)
    {
        $rekomendasi = $transaksi->where('status', 'Sukses')
            ->groupBy('kode_produk')
            ->map(function ($items) {
                $hargaJual = $items->sum('harga_jual');
                $laba = $items->sum('laba');
                $successRate = ($items->where('status', 'Sukses')->count() / $items->count()) * 100;
                
                return [
                    'kode_produk' => $items->first()->kode_produk,
                    'jumlah_transaksi' => $items->count(),
                    'total_penjualan' => (int)$hargaJual,
                    'total_laba' => (int)$laba,
                    'rata_laba' => (int)($laba / $items->count()),
                    'profit_margin' => $hargaJual > 0 ? round(($laba / $hargaJual) * 100, 2) : 0,
                    'success_rate' => round($successRate, 2),
                    'skor_rekomendasi' => $this->hitungSkorRekomendasi($laba, $hargaJual, $items),
                ];
            })
            ->sortByDesc('skor_rekomendasi')
            ->values();

        return $rekomendasi;
    }

    /**
     * Hitung skor rekomendasi produk (OLD)
     * Formula: (Profit Margin * 0.4) + (Success Rate * 0.3) + (Volume * 0.3)
     */
    private function hitungSkorRekomendasi($laba, $hargaJual, $items)
    {
        $profitMargin = $hargaJual > 0 ? ($laba / $hargaJual) * 100 : 0;
        $successRate = ($items->where('status', 'Sukses')->count() / $items->count()) * 100;
        $volume = min($items->count() / 100, 100); // Max 100 untuk normalisasi

        $skor = ($profitMargin * 0.4) + ($successRate * 0.3) + ($volume * 0.3);
        
        return round($skor, 2);
    }

    /**
     * ANALISIS: Performa Reseller (OLD - gunakan analisisResellerOptimized)
     */
    private function analisisReseller($transaksi)
    {
        return $transaksi->where('status', 'Sukses')
            ->groupBy('kode_reseller')
            ->map(function ($items) {
                return [
                    'kode_reseller' => $items->first()->kode_reseller ?? 'UNKNOWN',
                    'nama_reseller' => $items->first()->nama_reseller ?? 'Tidak diketahui',
                    'jumlah_transaksi' => $items->count(),
                    'total_penjualan' => (int)$items->sum('harga_jual'),
                    'total_laba' => (int)$items->sum('laba'),
                    'rata_laba' => (int)($items->sum('laba') / $items->count()),
                ];
            })
            ->sortByDesc('total_penjualan')
            ->take(10)
            ->values();
    }

    /**
     * Export analisis ke CSV
     */
    public function exportAnalisis(Request $request)
    {
        // Ambil semua data transaksi sesuai filter
        $transaksi = Transaksi::query();

        if ($request->input('start_date')) {
            $transaksi->whereDate('tgl_entri', '>=', $request->input('start_date'));
        }
        if ($request->input('end_date')) {
            $transaksi->whereDate('tgl_entri', '<=', $request->input('end_date'));
        }

        $rekomendasiProduk = $this->analisisRekomendasiProduk($transaksi->get());

        // Format CSV
        $filename = 'Analisis-Rekomendasi-Produk-' . now()->format('d-m-Y-His') . '.csv';
        
        $csv = "KODE PRODUK,JUMLAH TRANSAKSI,TOTAL PENJUALAN,TOTAL LABA,RATA-RATA LABA,PROFIT MARGIN (%),SUCCESS RATE (%),SKOR REKOMENDASI\n";
        
        foreach ($rekomendasiProduk as $produk) {
            $csv .= sprintf(
                "%s,%d,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f\n",
                $produk['kode_produk'],
                $produk['jumlah_transaksi'],
                $produk['total_penjualan'],
                $produk['total_laba'],
                $produk['rata_laba'],
                $produk['profit_margin'],
                $produk['success_rate'],
                $produk['skor_rekomendasi']
            );
        }

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            $filename,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }

    /**
     * Hapus semua data transaksi
     */
    public function clearData()
    {
        Transaksi::truncate();
        return redirect()->route('transaksi.upload')->with('success', 'Semua data transaksi telah dihapus');
    }
}
