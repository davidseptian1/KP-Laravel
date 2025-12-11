<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapBulananExport implements FromCollection, WithHeadings
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return DB::table('minusans')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->select(
                'tanggal',
                'server',
                'nama',
                'spl',
                'produk',
                'nomor',
                'total',
                'qty',
                'total_per_orang',
                'keterangan'
            )
            ->get()
            ->map(function ($item, $index) {
                return [
                    'No' => $index + 1,
                    'Tanggal' => $item->tanggal,
                    'Server' => $item->server,
                    'Nama' => $item->nama,
                    'SPL' => $item->spl,
                    'Produk' => $item->produk,
                    'Nomor' => $item->nomor,
                    'Total' => $item->total,
                    'Qty' => $item->qty,
                    'Total/Org' => $item->total_per_orang,
                    'Keterangan' => $item->keterangan,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Server',
            'Nama',
            'SPL',
            'Produk',
            'Nomor',
            'Total',
            'Qty',
            'Total/Org',
            'Keterangan'
        ];
    }
}
