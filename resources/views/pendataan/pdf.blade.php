<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendataan Transaksi</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: a4 landscape;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Header Section */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2b5797;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }

        .meta-box {
            text-align: right;
            font-size: 9px;
            color: #475569;
            line-height: 1.4;
        }

        /* KPI Summary Cards using Table */
        .kpi-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .kpi-cell {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            text-align: center;
            width: 33.33%;
        }

        .kpi-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .kpi-value-green {
            color: #15803d;
        }

        .kpi-value-blue {
            color: #1d4ed8;
        }

        /* Filter Info Bar */
        .filter-info {
            background-color: #f1f5f9;
            border-left: 3px solid #2563eb;
            padding: 6px 10px;
            font-size: 9px;
            color: #334155;
            margin-bottom: 12px;
            border-radius: 2px;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: top;
        }

        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .deskripsi-cell {
            max-width: 180px;
            word-wrap: break-word;
            word-break: break-all;
            white-space: pre-line;
            color: #475569;
            font-size: 8.5px;
        }

        .badge-nama {
            background-color: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Total Row */
        .total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
            font-size: 8.5px;
            color: #94a3b8;
            width: 100%;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <div class="title">Laporan Data Pendataan Transaksi</div>
                <div class="subtitle">Rekapitulasi pencatatan produk dan nominal transaksi staff</div>
            </td>
            <td class="meta-box">
                <div><strong>Waktu Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ auth()->user()->nama ?? auth()->user()->username ?? 'Staff' }}</div>
            </td>
        </tr>
    </table>

    <!-- Filter Notification (if any) -->
    @php
        $filterTexts = [];
        if (!empty($filters['shift']) && $filters['shift'] !== 'All Shift' && $filters['shift'] !== 'all') {
            $filterTexts[] = "Shift: <strong>" . e($filters['shift']) . "</strong>";
        } else {
            $filterTexts[] = "Shift: <strong>All Shift</strong>";
        }
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $sd = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'Awal';
            $ed = !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') : 'Sekarang';
            $filterTexts[] = "Periode: <strong>{$sd} s/d {$ed}</strong>";
        }
        if (!empty($filters['nama'])) {
            $filterTexts[] = "Nama: <strong>" . e($filters['nama']) . "</strong>";
        }
        if (!empty($filters['nama_produk'])) {
            $filterTexts[] = "Produk: <strong>" . e($filters['nama_produk']) . "</strong>";
        }
    @endphp

    @if(count($filterTexts) > 0)
        <div class="filter-info">
            Filter Aktif: {!! implode(' &nbsp;|&nbsp; ', $filterTexts) !!}
        </div>
    @endif

    <!-- Summary KPI Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-title">Total Pendataan</div>
                <div class="kpi-value kpi-value-blue">{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Total Qty Terdata</div>
                <div class="kpi-value">{{ number_format($totalQty, 0, ',', '.') }} Pcs / Item</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Total Nominal / Omset</div>
                <div class="kpi-value kpi-value-green">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 100px;">Tanggal & Jam</th>
                <th style="width: 80px;">Nama</th>
                <th style="width: 170px;">Nama Produk</th>
                <th class="text-right" style="width: 90px;">Postcal</th>
                <th class="text-center" style="width: 45px;">Qty</th>
                <th class="text-right" style="width: 95px;">Total Harga</th>
                <th>Rincian Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge-nama">{{ $item->nama }}</span>
                    </td>
                    <td class="fw-bold">{{ $item->nama_produk }}</td>
                    <td class="text-right">{{ $item->formatted_harga_qty }}</td>
                    <td class="text-center fw-bold">{{ $item->qty }}</td>
                    <td class="text-right fw-bold" style="color: #15803d;">{{ $item->formatted_total_harga }}</td>
                    <td class="deskripsi-cell">{{ \Illuminate\Support\Str::limit($item->deskripsi ?: '-', 120) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data pendataan yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($items->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right" style="letter-spacing: 0.5px;">TOTAL KESELURUHAN:</td>
                <td class="text-right">-</td>
                <td class="text-center">{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #15803d;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td>-</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Footer Information -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Laporan dibuat secara otomatis oleh sistem aplikasi • Fitur Pendataan Staff</td>
                <td class="text-right">Halaman 1 / 1</td>
            </tr>
        </table>
    </div>

</body>
</html>
