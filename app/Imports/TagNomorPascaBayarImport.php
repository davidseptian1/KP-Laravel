<?php

namespace App\Imports;

use App\Models\TagNomorPascaBayar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TagNomorPascaBayarImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            $cells = array_values($row->toArray());

            $nomor = $this->cleanString($cells[1] ?? null);
            $atasNama = $this->cleanString($cells[2] ?? null);

            if (!$nomor || !$atasNama) {
                continue;
            }

            // Skip header rows accidentally read as data
            if (str_contains(strtolower($nomor), 'nomor') || str_contains(strtolower($atasNama), 'atas nama')) {
                continue;
            }

            $payload = [
                'nomor' => $nomor,
                'atas_nama' => $atasNama,
                'chip' => $this->cleanString($cells[3] ?? null),
                'keterangan' => $this->cleanString($cells[4] ?? null),
                'bank' => $this->cleanString($cells[5] ?? null),
                'status' => $this->cleanString($cells[6] ?? null) ?: 'Aktif',
                'periode_des_2025_tagihan' => $this->parseNumber($cells[7] ?? null),
                'periode_des_2025_bank' => $this->cleanString($cells[8] ?? null),
                'periode_feb_2026_tanggal_payment' => $this->parseDate($cells[9] ?? null),
                'periode_feb_2026_tagihan' => $this->parseNumber($cells[10] ?? null),
            ];

            TagNomorPascaBayar::updateOrCreate(
                ['nomor' => $payload['nomor']],
                $payload
            );
        }
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function parseNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        $string = preg_replace('/[^0-9,\.\-]/', '', $string);
        if ($string === '' || $string === '-') {
            return null;
        }

        if (str_contains($string, ',')) {
            $string = str_replace('.', '', $string);
            $string = str_replace(',', '.', $string);
        }

        return is_numeric($string) ? (float) $string : null;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial date support
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('Y-m-d');
            } catch (\Throwable $e) {
                // continue
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
