<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendataanExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private Collection $items;
    private array $filters;

    public function __construct(Collection $items, array $filters = [])
    {
        $this->items = $items;
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal & Jam',
            'Nama',
            'Nama Produk',
            'Harga Qty (Satuan)',
            'Qty',
            'Total Harga',
            'Deskripsi Transaksi',
            'Ada Gambar',
            'Dicatat Oleh',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $totalQty = 0;
        $totalNominal = 0;
        $index = 1;

        foreach ($this->items as $item) {
            $qty = (int) ($item->qty ?? 1);
            $hargaQty = (float) ($item->harga_qty ?? 0);
            $totalHarga = (float) ($item->total_harga ?? 0);

            $totalQty += $qty;
            $totalNominal += $totalHarga;

            $rows[] = [
                $index++,
                optional($item->created_at)->format('d/m/Y H:i'),
                $this->sanitizeForExcel($item->nama ?? '-'),
                $this->sanitizeForExcel($item->nama_produk ?? '-'),
                $hargaQty,
                $qty,
                $totalHarga,
                $this->sanitizeForExcel($item->deskripsi ?? '-'),
                $item->gambar ? 'Ya' : 'Tidak',
                $this->sanitizeForExcel($item->user?->nama ?? '-'),
            ];
        }

        // Blank separator
        $rows[] = array_fill(0, 10, '');

        // Summary row
        $rows[] = [
            'TOTAL KESELURUHAN',
            '',
            '',
            '',
            '',
            $totalQty,
            $totalNominal,
            '',
            '',
            '',
        ];

        // Metadata rows
        $rows[] = ['Total Transaksi', $this->items->count(), '', '', '', '', '', '', '', ''];
        $rows[] = ['Waktu Download', now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', ''];

        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $periode = ($this->filters['start_date'] ?? 'Awal') . ' s/d ' . ($this->filters['end_date'] ?? 'Sekarang');
            $rows[] = ['Filter Periode', $periode, '', '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama'])) {
            $rows[] = ['Filter Nama', $this->filters['nama'], '', '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama_produk'])) {
            $rows[] = ['Filter Produk', $this->filters['nama_produk'], '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    /**
     * Prevent CSV/Excel formula injection.
     */
    private function sanitizeForExcel(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $first = $value !== '' ? $value[0] : '';
        if ($first === '=' || $first === '+' || $first === '-' || $first === '@') {
            return "'" . $value;
        }

        return $value;
    }
}
