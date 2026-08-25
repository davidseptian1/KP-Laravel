<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan Request PO ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .header h4 {
            margin: 5px 0 0 0;
            font-size: 13px;
            color: #2563eb;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            text-align: center;
        }
        .summary-box .title {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-box .val {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #374151;
        }
        table.data-table td {
            font-size: 10px;
        }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
        }
        .bg-success { background-color: #10b981; }
        .bg-primary { background-color: #3b82f6; }
        .bg-warning { background-color: #f59e0b; color: #000; }
        .bg-danger { background-color: #ef4444; }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN REKAP BULANAN REQUEST PO</h2>
        <h4>PERIODE: {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</h4>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="title">Total Request PO</div>
                <div class="val">{{ number_format($totalPO) }} Transaksi</div>
            </td>
            <td>
                <div class="title">Total Nominal PO</div>
                <div class="val" style="color: #059669;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="title">Disetujui / Selesai</div>
                <div class="val" style="color: #2563eb;">{{ number_format($totalApproved + $totalSelesai) }} PO</div>
            </td>
            <td>
                <div class="title">Pending / Tolak</div>
                <div class="val" style="color: #d97706;">{{ number_format($totalPending) }} / {{ number_format($totalRejected) }}</div>
            </td>
        </tr>
    </table>

    <h4 style="margin: 15px 0 5px 0; color: #1e293b;">RINCIAN TRANSAKSI PO</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="12%">Tgl PO</th>
                <th width="25%">Nama Supplier</th>
                <th width="13%">Server</th>
                <th width="12%">Pembayaran</th>
                <th width="13%">Cicilan</th>
                <th width="12%" class="text-end">Nominal (Rp)</th>
                <th width="8%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($list as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ optional($row->po_date)->format('d/m/Y') ?? optional($row->created_at)->format('d/m/Y') }}</td>
                    <td><strong>{{ $row->company_name ?? $row->owner_name }}</strong></td>
                    <td class="text-center">{{ $row->division ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper($row->payment_method ?? '-') }}</td>
                    <td class="text-center">{{ $row->cicilan }}</td>
                    <td class="text-end"><strong>Rp {{ number_format($row->total_amount, 2, ',', '.') }}</strong></td>
                    <td class="text-center">
                        @if(($row->status ?? 'pending') === 'approved')
                            <span class="badge bg-success">ACC</span>
                        @elseif(($row->status ?? 'pending') === 'selesai')
                            <span class="badge bg-primary">Selesai</span>
                        @elseif(($row->status ?? 'pending') === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada transaksi Request PO pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">TOTAL NOMINAL PERIODE:</th>
                <th class="text-end" style="color: #059669; font-size: 11px;">Rp {{ number_format($totalNominal, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak secara otomatis dari Sistem e-SMT pada {{ date('d/m/Y H:i') }} WIB
    </div>

</body>
</html>
