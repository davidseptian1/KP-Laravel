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
            'D' => 38,  // NAMA PRODUK
            'E' => 14,  // JENIS CHIP (KTTS / KBTG)
            'F' => 16,  // STATUS (Pending / Sukses / Gagal / Dihapus)
            'G' => 18,  // POSTCAL
            'H' => 12,  // QTY
            'I' => 22,  // TOTAL HARGA
            'J' => 15,  // ADA GAMBAR
            'K' => 18,  // DICATAT OLEH
            'L' => 35,  // ALASAN EDIT
            'M' => 35,  // ALASAN HAPUS
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
            'JENIS CHIP',
            'STATUS',
            'POSTCAL',
            'QTY',
            'TOTAL HARGA',
            'ADA GAMBAR',
            'DICATAT OLEH',
            'ALASAN EDIT',
            'ALASAN HAPUS',
        ];

        $totalQty = 0;
        $totalNominal = 0;
        $totalDihapus = 0;
        $totalSusulan = 0;
        $index = 1;

        if ($this->items->count() > 0) {
            foreach ($this->items as $item) {
                $isDeleted = $item->trashed();
                $isSusulan = !$isDeleted && (bool) ($item->is_susulan ?? false);
                $qty = (int) ($item->qty ?? 1);
                $hargaQty = (float) ($item->harga_qty ?? 0);
                $totalHarga = (float) ($item->total_harga ?? 0);

                if (!$isDeleted) {
                    $totalQty += $qty;
                    $totalNominal += $totalHarga;
                    if ($isSusulan) {
                        $totalSusulan++;
                    }
                } else {
                    $totalDihapus++;
                }

                $statusText = $isDeleted ? 'DIHAPUS' : ($item->status_label ?? 'Pending');
                if ($isSusulan) {
                    $statusText .= ' [SUSULAN]';
                }

                $tanggalJam = optional($item->created_at)->format('d/m/Y H:i');
                if ($isSusulan) {
                    $tanggalJam .= ' (Susulan)';
                }

                $rows[] = [
                    $index++,
                    $tanggalJam,
                    $this->sanitizeForExcel($item->nama ?? '-'),
                    $this->sanitizeForExcel($item->nama_produk ?? '-'),
                    $this->sanitizeForExcel($item->jenis_chip ?? 'KTTS'),
                    $this->sanitizeForExcel($statusText),
                    'Rp. ' . number_format($hargaQty, 0, ',', '.'),
                    $qty,
                    'Rp. ' . number_format($totalHarga, 0, ',', '.'),
                    $item->gambar ? 'Ya' : 'Tidak',
                    $this->sanitizeForExcel($item->user?->nama ?? '-'),
                    $this->sanitizeForExcel($item->alasan_edit ? str_replace(["\r\n", "\r", "\n"], ' | ', $item->alasan_edit) : '-'),
                    $this->sanitizeForExcel($isDeleted && $item->alasan_hapus ? str_replace(["\r\n", "\r", "\n"], ' | ', $item->alasan_hapus) : '-'),
                ];
            }
        } else {
            $rows[] = ['Tidak ada data transaksi yang ditemukan.', '', '', '', '', '', '', '', '', '', '', '', ''];
        }

        // Total Row (Directly below data - reflects active transactions)
        $rows[] = [
            'TOTAL KESELURUHAN (AKTIF)',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalQty,
            'Rp. ' . number_format($totalNominal, 0, ',', '.'),
            '-',
            '-',
            '-',
            '-',
        ];

        // Metadata footer section
        $totalAktifText = ($this->items->count() - $totalDihapus) . ' Data Aktif';
        if ($totalSusulan > 0) {
            $totalAktifText .= " ({$totalSusulan} Susulan Kemarin)";
        }
        if ($totalDihapus > 0) {
            $totalAktifText .= " ({$totalDihapus} Dihapus)";
        }

        $rows[] = array_fill(0, 13, '');
        $rows[] = ['Total Transaksi', ': ' . $totalAktifText, '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Waktu Download', ': ' . now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', '', ''];

        $rows[] = ['Filter Shift', ': ' . (!empty($this->filters['shift']) ? $this->filters['shift'] : 'All Shift (Semua Shift)'), '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Filter Jenis Chip', ': ' . (!empty($this->filters['jenis_chip']) ? $this->filters['jenis_chip'] : 'All Chip (Semua Chip)'), '', '', '', '', '', '', '', '', '', '', ''];
        if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $sd = !empty($this->filters['start_date']) ? Carbon::parse($this->filters['start_date'])->format('d/m/Y') : 'Awal';
            $ed = !empty($this->filters['end_date']) ? Carbon::parse($this->filters['end_date'])->format('d/m/Y') : 'Sekarang';
            $rows[] = ['Filter Periode', ": {$sd} s/d {$ed}", '', '', '', '', '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama'])) {
            $rows[] = ['Filter Nama', ': ' . $this->filters['nama'], '', '', '', '', '', '', '', '', '', '', ''];
        }
        if (!empty($this->filters['nama_produk'])) {
            $rows[] = ['Filter Produk', ': ' . $this->filters['nama_produk'], '', '', '', '', '', '', '', '', '', '', ''];
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
                $sheet->getStyle("A{$headerRow}:M{$headerRow}")->applyFromArray([
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
                    $rowIdx = $dataStartRow;
                    foreach ($this->items as $item) {
                        $r = $rowIdx++;
                        $sheet->getRowDimension($r)->setRowHeight(22);

                        if ($item->trashed()) {
                            // RED COLOR HIGHLIGHT FOR DELETED DATA
                            $sheet->getStyle("A{$r}:M{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FEE2E2'], // Soft pastel red background
                                ],
                                'font' => [
                                    'color' => ['rgb' => 'DC2626'],     // Bold dark red font
                                    'bold' => true,
                                ],
                            ]);
                            $sheet->getStyle("D{$r}")->getFont()->setStrikethrough(true);
                        } elseif ($item->is_susulan ?? false) {
                            // YELLOW COLOR HIGHLIGHT FOR DATA SUSULAN KEMARIN
                            $sheet->getStyle("A{$r}:M{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FEF9C3'], // Warm pastel yellow background
                                ],
                                'font' => [
                                    'color' => ['rgb' => '854D0E'],     // Dark amber / brown font
                                ],
                            ]);
                            $sheet->getStyle("D{$r}")->getFont()->setBold(true);
                        } elseif ($r % 2 === 1) {
                            // Subtle Zebra striping
                            $sheet->getStyle("A{$r}:M{$r}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8FAFC'],
                                ],
                            ]);
                        }
                    }

                    // Table Data Borders & Default Font
                    $sheet->getStyle("A{$dataStartRow}:M{$dataEndRow}")->applyFromArray([
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
                    $sheet->getStyle("E{$dataStartRow}:E{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$dataStartRow}:F{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // STATUS
                    $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);  // POSTCAL
                    $sheet->getStyle("H{$dataStartRow}:H{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // QTY
                    $sheet->getStyle("I{$dataStartRow}:I{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);  // TOTAL HARGA
                    $sheet->getStyle("J{$dataStartRow}:J{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // GAMBAR
                    $sheet->getStyle("K{$dataStartRow}:K{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // DICATAT OLEH
                    $sheet->getStyle("L{$dataStartRow}:L{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);   // ALASAN EDIT
                    $sheet->getStyle("M{$dataStartRow}:M{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);   // ALASAN HAPUS

                    // Bold for Total Harga column
                    $sheet->getStyle("I{$dataStartRow}:I{$dataEndRow}")->getFont()->setBold(true);
                } else {
                    $sheet->mergeCells("A{$dataStartRow}:M{$dataStartRow}");
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
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
                $sheet->getRowDimension($totalRow)->setRowHeight(26);
                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
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
                $sheet->getStyle("H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("I{$totalRow}")->getFont()->getColor()->setRGB('15803D'); // Dark Green accent for total price
                $sheet->getStyle("J{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
