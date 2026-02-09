<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Reimburse</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; }
        .summary { margin-top: 10px; }
        .summary td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <h2>Rekap Reimburse</h2>
    <div>Periode: {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</div>

    <table class="summary">
        <tr><td>Total</td><td>: {{ $summary['total'] }}</td></tr>
        <tr><td>Pending</td><td>: {{ $summary['pending'] }}</td></tr>
        <tr><td>Approved</td><td>: {{ $summary['approved'] }}</td></tr>
        <tr><td>Rejected</td><td>: {{ $summary['rejected'] }}</td></tr>
        <tr><td>Revision</td><td>: {{ $summary['revision'] }}</td></tr>
        <tr><td>Nominal</td><td>: Rp {{ number_format($summary['nominal'], 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Divisi</th>
                <th>Barang</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->kode_reimburse }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->divisi }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ optional($item->tanggal_pengajuan)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
