<?php

namespace App\Imports;

use App\Models\TagPlnInternet;
use App\Models\TagPlnInternetPeriod;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TagPlnInternetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $cells = array_values($row->toArray());

            $nama = $this->cleanString($cells[1] ?? null);
            $nomor = $this->cleanString($cells[2] ?? null);
            $atasNama = $this->cleanString($cells[3] ?? null);

            if (!$nama || !$nomor || !$atasNama) {
                continue;
            }

            if (str_contains(strtolower($nomor), 'nomor')) {
                continue;
            }

            $payload = [
                'nama' => $nama,
                'nomor_pln_internet' => $nomor,
                'atas_nama' => $atasNama,
                'bank' => $this->cleanString($cells[4] ?? null),
                'keterangan' => $this->cleanString($cells[5] ?? null),
                'periode_januari_2026_tagihan' => $this->parseNumber($cells[6] ?? null),
                'periode_januari_2026_tanggal_payment' => $this->parseDate($cells[7] ?? null),
                'periode_februari_2026_tagihan' => $this->parseNumber($cells[8] ?? null),
                'periode_februari_2026_tanggal_payment' => $this->parseDate($cells[9] ?? null),
            ];

            $item = TagPlnInternet::updateOrCreate(
                ['nomor_pln_internet' => $payload['nomor_pln_internet']],
                $payload
            );

            if ($payload['periode_januari_2026_tagihan'] !== null || !empty($payload['periode_januari_2026_tanggal_payment'])) {
                TagPlnInternetPeriod::updateOrCreate(
                    [
                        'tag_pln_internet_id' => $item->id,
                        'periode_bulan' => 1,
                        'periode_tahun' => 2026,
                    ],
                    [
                        'tagihan' => $payload['periode_januari_2026_tagihan'],
                        'tanggal_payment' => $payload['periode_januari_2026_tanggal_payment'],
                    ]
                );
            }

            if ($payload['periode_februari_2026_tagihan'] !== null || !empty($payload['periode_februari_2026_tanggal_payment'])) {
                TagPlnInternetPeriod::updateOrCreate(
                    [
                        'tag_pln_internet_id' => $item->id,
                        'periode_bulan' => 2,
                        'periode_tahun' => 2026,
                    ],
                    [
                        'tagihan' => $payload['periode_februari_2026_tagihan'],
                        'tanggal_payment' => $payload['periode_februari_2026_tanggal_payment'],
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

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $text = trim((string) $value);
        if ($text === '') return null;

        foreach (['l, d F Y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
