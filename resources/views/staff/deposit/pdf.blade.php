<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Request Deposit Staff</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
            background: #f8fbff;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #0f2f57;
        }

        .meta {
            font-size: 10px;
            color: #444;
            line-height: 1.6;
        }

        .summary {
            margin-top: 8px;
            padding: 8px;
            border-radius: 6px;
            background: #fff3cd;
            border: 1px solid #ffe69c;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #eef4fb;
            color: #0f2f57;
            font-weight: 700;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Request Deposit Staff</div>
        <div class="meta">Periode harian: {{ $filters['tanggal'] }} (00:00 - 23:59)</div>
        <div class="meta">Email Staff: {{ $staffEmail }}</div>
        <div class="meta">Jam Laporan Download: {{ $downloadedAt->format('d/m/Y H:i:s') }}</div>
        <div class="summary">
            Total Keseluruhan Deposit: <strong>Rp {{ number_format((float) $totalDeposit, 0, ',', '.') }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NAMA SUPPLIER</th>
                <th>JENIS</th>
                <th>NOMINAL</th>
                <th>BANK</th>
                <th>SERVER</th>
                <th>NOREK</th>
                <th>NAMA REKENING</th>
                <th>Reply Tiket</th>
                <th>REPLY PENAMBAHAN</th>
                <th>Bukti Tranfers Admin</th>
                <th>STATUS</th>
                <th>JAM</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $replyTiket = trim((string) ($item->reply_tiket ?? ''));
                    if (!empty($item->reply_tiket_image)) {
                        $replyTiket = $replyTiket !== '' ? $replyTiket . ' [Image]' : 'Image';
                    }

                    $replyPenambahan = trim((string) ($item->reply_penambahan ?? ''));
                    if (!empty($item->reply_penambahan_image)) {
                        $replyPenambahan = $replyPenambahan !== '' ? $replyPenambahan . ' [Image]' : 'Image';
                    }

                    $buktiTransferAdmin = '-';
                    if (($item->bukti_transfer_admin_type ?? 'text') === 'image') {
                        $buktiTransferAdmin = trim((string) ($item->bukti_transfer_admin_text ?? '')) !== ''
                            ? trim((string) $item->bukti_transfer_admin_text) . ' [Image]'
                            : 'Image';
                    } elseif (!empty($item->bukti_transfer_admin_text)) {
                        $buktiTransferAdmin = $item->bukti_transfer_admin_text;
                    }
                @endphp
                <tr>
                    <td>{{ $item->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $item->nama_supplier }}</td>
                    <td>{{ strtoupper((string) ($item->jenis_transaksi ?? 'deposit')) }}</td>
                    <td class="text-right">Rp {{ number_format((float) ($item->nominal ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $item->bank }}</td>
                    <td>{{ $item->server }}</td>
                    <td>{{ $item->no_rek }}</td>
                    <td>{{ $item->nama_rekening }}</td>
                    <td>{{ $replyTiket !== '' ? $replyTiket : '-' }}</td>
                    <td>{{ $replyPenambahan !== '' ? $replyPenambahan : '-' }}</td>
                    <td>{{ $buktiTransferAdmin }}</td>
                    <td>{{ ucfirst((string) ($item->status ?? 'pending')) }}</td>
                    <td>{{ $item->jam ? date('H:i', strtotime((string) $item->jam)) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;">Tidak ada data request deposit.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
