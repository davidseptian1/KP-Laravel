<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepositMonitoringExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Tanggal',
            'Nama Supplier',
            'Nama Rekening',
            'Bank',
            'Bank Tujuan',
            'Server',
            'No Rek',
            'Nominal',
            'Bukti Tiket',
            'Bukti Penambahan',
            'Bukti Transfers Admin',
            'Bukti Bayar Hutang',
            'Status',
            'Jam',
        ];
    }

    public function map($item): array
    {
        $buktiTransferAdmin = '-';

        if (($item->bukti_transfer_admin_type ?? 'text') === 'image') {
            $buktiTransferAdmin = trim((string) ($item->bukti_transfer_admin_text ?? '')) !== ''
                ? trim((string) $item->bukti_transfer_admin_text)
                : 'Image';
        } elseif (!empty($item->bukti_transfer_admin_text)) {
            $buktiTransferAdmin = $item->bukti_transfer_admin_text;
        }
        $buktiTransferAdmin = $this->sanitizeForExcel($buktiTransferAdmin);

        $buktiBayarHutang = '-';
        if (($item->bukti_bayar_hutang_type ?? 'text') === 'image') {
            $buktiBayarHutang = trim((string) ($item->bukti_bayar_hutang_text ?? '')) !== ''
                ? trim((string) $item->bukti_bayar_hutang_text)
                : 'Image';
        } elseif (!empty($item->bukti_bayar_hutang_text)) {
            $buktiBayarHutang = $item->bukti_bayar_hutang_text;
        }
        $buktiBayarHutang = $this->sanitizeForExcel($buktiBayarHutang);

        return [
            optional($item->created_at)->format('d/m/Y H:i'),
            $item->nama_supplier,
            $item->nama_rekening,
            $item->bank,
            $item->bank_tujuan ?? '-',
            $item->server,
            $item->no_rek,
            (float) ($item->nominal ?? 0),
            $this->sanitizeForExcel($item->reply_tiket ?? '-'),
            $this->sanitizeForExcel($item->reply_penambahan ?? '-'),
            $buktiTransferAdmin,
            $buktiBayarHutang,
            ($item->status ?? 'pending') === 'selesai_belum_lunas' ? 'Selesai (Belum Lunas)' : ucfirst((string) ($item->status ?? 'pending')),
            $item->jam ? date('H:i', strtotime((string) $item->jam)) : '-',
        ];
    }

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
