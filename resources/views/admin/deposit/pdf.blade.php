<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Deposit</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        h2 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .subtitle {
            margin-bottom: 12px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>Monitoring Deposit</h2>
    <div class="subtitle">
        Periode tanggal: {{ $rangeLabel }} (jam 00:00 - 23:59)
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Supplier</th>
                <th>Nama Rekening</th>
                <th>Bank</th>
                <th>Server</th>
                <th>No Rek</th>
                <th>Nominal</th>
                <th>Bukti Tiket</th>
                <th>Bukti Penambahan</th>
                <th>Bukti Transfers Admin</th>
                <th>Status</th>
                <th>Jam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $buktiTransferAdmin = '-';
                    if (($item->bukti_transfer_admin_type ?? 'text') === 'image') {
                        $buktiTransferAdmin = trim((string) ($item->bukti_transfer_admin_text ?? '')) !== ''
                            ? trim((string) $item->bukti_transfer_admin_text)
                            : 'Image';
                    } elseif (!empty($item->bukti_transfer_admin_text)) {
                        $buktiTransferAdmin = $item->bukti_transfer_admin_text;
                    }
                @endphp
                <tr>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->nama_supplier }}</td>
                    <td>{{ $item->nama_rekening }}</td>
                    <td>{{ $item->bank }}</td>
                    <td>{{ $item->server }}</td>
                    <td>{{ $item->no_rek }}</td>
                    <td class="text-right">{{ number_format((float) ($item->nominal ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $item->reply_tiket ?? '-' }}</td>
                    <td>{{ $item->reply_penambahan ?? '-' }}</td>
                    <td>{{ $buktiTransferAdmin }}</td>
                    <td>{{ ($item->status ?? 'pending') === 'selesai' ? 'Selesai (Belum Lunas)' : ucfirst((string) ($item->status ?? 'pending')) }}</td>
                    <td>{{ $item->jam ? date('H:i', strtotime((string) $item->jam)) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
