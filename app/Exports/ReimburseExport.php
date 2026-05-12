<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ReimburseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Kode Reimburse',
            'Kode Form',
            'Nama User',
            'Divisi',
            'No Rekening/ID',
            'Metode',
            'Provider/Bank',
            'Atas Nama',
            'Nama Barang',
            'Deskripsi',
            'Nominal',
            'WA Penerima',
            'WA Pengisi',
            'Tanggal Pengajuan',
            'Status',
            'Catatan Admin',
        ];
    }

    public function map($item): array
    {
        return [
            $item->kode_reimburse,
            $item->form?->kode_form ?? '-',
            $item->nama ?? '-',
            $item->divisi ?? '-',
            $item->payment_account_number ?? $item->no_rekening ?? '-',
            $item->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank',
            strtoupper($item->payment_provider ?? '-'),
            $item->payment_account_name ?? '-',
            $item->nama_barang ?? '-',
            $item->keterangan ?? '-',
            (float) ($item->nominal ?? 0),
            $item->wa_penerima ?? '-',
            $item->wa_pengisi ?? '-',
            $item->tanggal_pengajuan ? Carbon::parse($item->tanggal_pengajuan)->format('d/m/Y H:i') : '-',
            ucfirst($item->status),
            $item->catatan_admin ?? '-',
        ];
    }
}
