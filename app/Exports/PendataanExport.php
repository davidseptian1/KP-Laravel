<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PendataanExport implements FromArray, WithColumnWidths, WithEvents
{
    private Collection $items;
    private array $filters;

    public function __construct(Collection $items, array $filters = [])
    {
        $this->items = $items;
        $this->filters = $filters;
    }

    /**
     * Set explicit, comfortable column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // NO
            'B' => 20,  // TANGGAL & JAM
            'C' => 24,  // NAMA (e.g. Nuni ( Shift 1 ))
            'D' => 40,  // NAMA PRODUK
            'E' => 18,  // POSTCAL
            'F' => 12,  // QTY
            'G' => 22,  // TOTAL HARGA
            'H' => 15,  // ADA GAMBAR
            'I' => 18,  // DICATAT OLEH
            'J' => 35,  // ALASAN EDIT
        ];
    }

    /**
     * Construct formatted array rows for Excel.
     */
    public function array(): array
    {
        $rows = [];

        // Row 1: Clean, Professional Header Row
        $rows[] = [
            'NO',
            'TANGGAL & JAM',
            'NAMA',
            'NAMA PRODUK',
            'POSTCAL',
            'QTY',
            'TOTAL HARGA',
            'ADA GAMBAR',
            'DICATAT OLEH',
            'ALASAN EDIT',
        ];

        $totalQty = 0;
        $totalNominal = 0;
        $index = 1;

        if ($this->items->count() > 0) {
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
                    'Rp. ' . number_format($hargaQty, 0, ',', '.'),
                    $qty,
                    'Rp. ' . number_format($totalHarga, 0, ',', '.'),
                    $item->gambar ? 'Ya' : 'Tidak',
                    $this->sanitizeForExcel($item->user?->nama ?? '-'),
                    $this->sanitizeForExcel($item->alasan_edit ? str_replace(["\r\n", "\r", "\n"], ' | ', $item->alasan_edit) : '-'),
                ];
            }
        } else {
            $rows[] = ['Tidak ada data transaksi yang ditemukan.', '', '', '', '', '', '', '', '', ''];
        }

        // Total Row (Directly below data)
        $rows[] = [
            'TOTAL KESELURUHAN',
            '',
            '',
            '',
            '',
            $totalQty,
            'Rp. ' . number_format($totalNominal, 0, ',', '.'),
            '-',
            '-',
            '-',
        ];

        // Metadata footer section
        $rows[] = array_fill(0, 10, '');
        $rows[] = ['Total Transaksi', ': ' . $this->items->count() . ' Data', '', '', '', '', '', '', '', ''];
        $rows[] = ['Waktu Download', ': ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', ''];

        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $sd = !empty($this->filters['start_date']) ? Carbon::parse($this->filters['start_date'])->format('d/m/Y') : 'Awal';
            $ed = !empty($this->filters['end_date']) ? Carbon::parse($this->filters['end_date'])->format('d/m/Y') : 'Sekarang';
            $rows[] = ['Filter Periode', ": {$sd} s/d {$ed}", '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama'])) {
            $rows[] = ['Filter Nama', ': ' . $this->filters['nama'], '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama_produk'])) {
            $rows[] = ['Filter Produk', ': ' . $this->filters['nama_produk'], '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    /**
     * Apply professional styling to the sheet.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $itemCount = $this->items->count();
                $headerRow = 1;
                $dataStartRow = 2;
                $dataEndRow = $itemCount > 0 ? ($dataStartRow + $itemCount - 1) : 2;
                $totalRow = $dataEndRow + 1;
                $metaStartRow = $totalRow + 2;
                $highestRow = $sheet->getHighestRow();

                // 1. Header Row Styling (Row 1)
                $sheet->getRowDimension($headerRow)->setRowHeight(32);
                $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A8A'], // Elegant Deep Navy Blue
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => false,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '172554'],
                        ],
                    ],
                ]);

                // 2. Data Rows Styling
                if ($itemCount > 0) {
                    for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(22);

                        // Subtle Zebra striping
                        if ($r % 2 === 1) {
                            $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8FAFC'],
                                ],
                            ]);
                        }
                    }

                    // Table Data Borders & Default Font
                    $sheet->getStyle("A{$dataStartRow}:J{$dataEndRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);

                    // Data Alignments
                    $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$dataStartRow}:C{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$dataStartRow}:D{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("E{$dataStartRow}:E{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("F{$dataStartRow}:F{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("H{$dataStartRow}:H{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("I{$dataStartRow}:I{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("J{$dataStartRow}:J{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Bold for Total Harga column
                    $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")->getFont()->setBold(true);
                } else {
                    $sheet->mergeCells("A{$dataStartRow}:J{$dataStartRow}");
                    $sheet->getRowDimension($dataStartRow)->setRowHeight(24);
                    $sheet->getStyle("A{$dataStartRow}")->applyFromArray([
                        'font' => ['italic' => true, 'color' => ['rgb' => '64748B']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                        ],
                    ]);
                }

                // 3. Total Row Styling
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->getRowDimension($totalRow)->setRowHeight(26);
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '0F172A'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'], // Clean slate background
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '0F172A'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_DOUBLE,
                            'color' => ['rgb' => '0F172A'],
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G{$totalRow}")->getFont()->getColor()->setRGB('15803D'); // Dark Green accent for total price
                $sheet->getStyle("H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Metadata footer styling
                for ($m = $metaStartRow; $m <= $highestRow; $m++) {
                    $sheet->getRowDimension($m)->setRowHeight(18);
                    $sheet->getStyle("A{$m}:B{$m}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 9.5, 'color' => ['rgb' => '475569']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("A{$m}")->getFont()->setBold(true);
                }
            },
        ];
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
