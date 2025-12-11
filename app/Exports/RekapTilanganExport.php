<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapTilanganExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Fetch rows in range then split names and aggregate per individual
        $rows = DB::table('minusans')
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->select('nama', 'total', 'qty', 'total_per_orang')
            ->get();

        $map = [];

        foreach ($rows as $row) {
            $names = preg_split('/\s*,\s*/u', trim($row->nama));
            // Determine per-person share: prefer total_per_orang when present and > 0
            $share = null;
            if (!is_null($row->total_per_orang) && is_numeric($row->total_per_orang) && floatval($row->total_per_orang) > 0) {
                $share = floatval($row->total_per_orang);
            } else {
                // fallback: distribute total equally by qty if present, otherwise by number of names
                if (!is_null($row->qty) && intval($row->qty) > 0) {
                    $share = floatval($row->total) / intval($row->qty);
                } else {
                    $count = max(1, count($names));
                    $share = floatval($row->total) / $count;
                }
            }

            foreach ($names as $name) {
                $key = mb_strtolower(trim($name));
                if ($key === '') continue;
                if (!isset($map[$key])) {
                    // store display name as capitalized
                    $map[$key] = [
                        'display' => mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
                        'total' => 0.0,
                    ];
                }
                $map[$key]['total'] += $share;
            }
        }

        // Convert map to collection and sort by total desc
        $result = collect($map)
            ->map(function ($item, $key) {
                return [
                    'nama' => $item['display'],
                    'total_tilangan' => round($item['total'], 2),
                ];
            })
            ->sortByDesc('total_tilangan')
            ->values();

        return $result->map(function ($item, $index) {
            return [
                'No' => $index + 1,
                'Nama' => $item['nama'],
                'Total Tilangan' => $item['total_tilangan'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Total Tilangan',
        ];
    }
}
