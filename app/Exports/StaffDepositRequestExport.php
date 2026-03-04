<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StaffDepositRequestExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private Collection $items;
    private float $totalDeposit;
    private Carbon $downloadedAt;
    private string $staffEmail;

    public function __construct(Collection $items, float $totalDeposit, Carbon $downloadedAt, string $staffEmail)
    {
        $this->items = $items;
        $this->totalDeposit = $totalDeposit;
        $this->downloadedAt = $downloadedAt;
        $this->staffEmail = $staffEmail;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NAMA SUPPLIER',
            'JENIS',
            'NOMINAL',
            'BANK',
            'BANK TUJUAN',
            'SERVER',
            'NOREK',
            'NAMA REKENING',
            'Reply Tiket',
            'REPLY PENAMBAHAN',
            'Bukti Tranfers Admin',
            'STATUS',
            'JAM',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->items as $item) {
            $replyTiket = trim((string) ($item->reply_tiket ?? ''));
            if (!empty($item->reply_tiket_image)) {
                $replyTiket = $replyTiket !== ''
                    ? $replyTiket . ' [Image]'
                    : 'Image';
            }

            $replyPenambahan = trim((string) ($item->reply_penambahan ?? ''));
            if (!empty($item->reply_penambahan_image)) {
                $replyPenambahan = $replyPenambahan !== ''
                    ? $replyPenambahan . ' [Image]'
                    : 'Image';
            }

            $buktiTransferAdmin = '-';
            if (($item->bukti_transfer_admin_type ?? 'text') === 'image') {
                $buktiTransferAdmin = trim((string) ($item->bukti_transfer_admin_text ?? '')) !== ''
                    ? trim((string) $item->bukti_transfer_admin_text) . ' [Image]'
                    : 'Image';
            } elseif (!empty($item->bukti_transfer_admin_text)) {
                $buktiTransferAdmin = $item->bukti_transfer_admin_text;
            }

            $rows[] = [
                optional($item->created_at)->format('d/m/Y'),
                $item->nama_supplier,
                strtoupper((string) ($item->jenis_transaksi ?? 'deposit')),
                'Rp ' . number_format((float) ($item->nominal ?? 0), 0, ',', '.'),
                $item->bank,
                $item->bank_tujuan ?? '-',
                $item->server,
                $item->no_rek,
                $item->nama_rekening,
                $replyTiket !== '' ? $replyTiket : '-',
                $replyPenambahan !== '' ? $replyPenambahan : '-',
                $buktiTransferAdmin,
                ucfirst((string) ($item->status ?? 'pending')),
                $item->jam ? date('H:i', strtotime((string) $item->jam)) : '-',
            ];
        }

        $rows[] = array_fill(0, 14, '');
        $rows[] = ['Total Keseluruhan Deposit', '', '', 'Rp ' . number_format($this->totalDeposit, 0, ',', '.'), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Jam Laporan Download', '', '', $this->downloadedAt->format('d/m/Y H:i:s'), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Email Staff', '', '', $this->staffEmail, '', '', '', '', '', '', '', '', '', ''];

        return $rows;
    }
}
