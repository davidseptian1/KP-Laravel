<?php

namespace App\Imports;

use App\Models\TagLainnya;
use App\Models\TagLainnyaPeriod;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TagLainnyaImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $cells = array_values($row->toArray());

            $nama = $this->cleanString($cells[1] ?? null);
            $noRekeningVa = $this->cleanString($cells[2] ?? null);

            if (!$nama || !$noRekeningVa) {
                continue;
            }

            if (str_contains(strtolower($noRekeningVa), 'rekening') || str_contains(strtolower($noRekeningVa), 'va')) {
                continue;
            }

            $item = TagLainnya::updateOrCreate(
                ['no_rekening_va' => $noRekeningVa],
                [
                    'nama' => $nama,
                    'jumlah' => $this->parseNumber($cells[3] ?? null),
                    'bank' => $this->cleanString($cells[4] ?? null),
                    'keterangan' => $this->cleanString($cells[5] ?? null),
                ]
            );

            $tagihanJan = $this->parseNumber($cells[6] ?? null);
            $tanggalJan = $this->parseDateTime($cells[7] ?? null);
            if ($tagihanJan !== null || $tanggalJan !== null) {
                TagLainnyaPeriod::updateOrCreate(
                    [
                        'tag_lainnya_id' => $item->id,
                        'periode_bulan' => 1,
                        'periode_tahun' => 2026,
                    ],
                    [
                        'tagihan' => $tagihanJan,
                        'tanggal_payment' => $tanggalJan,
                    ]
                );
            }

            $tagihanFeb = $this->parseNumber($cells[8] ?? null);
            $tanggalFeb = $this->parseDateTime($cells[9] ?? null);
            if ($tagihanFeb !== null || $tanggalFeb !== null) {
                TagLainnyaPeriod::updateOrCreate(
                    [
                        'tag_lainnya_id' => $item->id,
                        'periode_bulan' => 2,
                        'periode_tahun' => 2026,
                    ],
                    [
                        'tagihan' => $tagihanFeb,
                        'tanggal_payment' => $tanggalFeb,
                    ]
                );
            }
        }
    }

    private function cleanString($value): ?string
    {
        if ($value === null) return null;
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function parseNumber($value): ?float
    {
        if ($value === null || $value === '') return null;

        $string = trim((string) $value);
        if ($string === '') return null;

        $string = preg_replace('/[^0-9,\.\-]/', '', $string);
        if ($string === '' || $string === '-') return null;

        if (str_contains($string, ',')) {
            $string = str_replace('.', '', $string);
            $string = str_replace(',', '.', $string);
        }

        return is_numeric($string) ? (float) $string : null;
    }

    private function parseDateTime($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value))->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $text = trim((string) $value);
        if ($text === '') return null;

        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d H:i:s', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
