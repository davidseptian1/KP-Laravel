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

        .badge-chip {
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
            text-transform: uppercase;
        }

        .badge-chip-ktts {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-chip-kbtg {
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }

        /* Total Row */
        .total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 10px;
        }

        /* Deleted Row Styling */
        .row-deleted {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        .row-deleted td {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
            border-color: #fca5a5 !important;
        }

        .strike-text {
            text-decoration: line-through;
            color: #b91c1c !important;
        }

        .badge-status-deleted {
            background-color: #dc2626;
            color: #ffffff;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            text-transform: uppercase;
            display: inline-block;
        }

        .alasan-hapus-box {
            margin-top: 3px;
            font-size: 8px;
            color: #991b1b;
            background-color: #fecaca;
            border-left: 2px solid #ef4444;
            padding: 2px 4px;
            border-radius: 2px;
            line-height: 1.2;
            white-space: pre-line;
        }

        /* Susulan Row Styling (Kuning) */
        .row-susulan {
            background-color: #fef9c3 !important;
            color: #854d0e !important;
        }

        .row-susulan td {
            background-color: #fef9c3 !important;
            color: #854d0e !important;
            border-color: #fde047 !important;
        }

        .badge-status-susulan {
            background-color: #fef08a;
            color: #854d0e;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            border: 1px solid #fde047;
            display: inline-block;
            margin-top: 2px;
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
        if (!empty($filters['jenis_chip']) && $filters['jenis_chip'] !== 'All Chip' && $filters['jenis_chip'] !== 'all') {
            $filterTexts[] = "Chip: <strong>" . e($filters['jenis_chip']) . "</strong>";
        } else {
            $filterTexts[] = "Chip: <strong>All Chip</strong>";
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
    @php
        $kpiCols = 3 + (isset($totalDihapus) && $totalDihapus > 0 ? 1 : 0) + (isset($totalSusulan) && $totalSusulan > 0 ? 1 : 0);
        $kpiWidth = round(100 / $kpiCols, 2) . '%';
    @endphp
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell" style="width: {{ $kpiWidth }};">
                <div class="kpi-title">Total Pendataan Aktif</div>
                <div class="kpi-value kpi-value-blue">{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</div>
            </td>
            <td class="kpi-cell" style="width: {{ $kpiWidth }};">
                <div class="kpi-title">Total Qty Terdata</div>
                <div class="kpi-value">{{ number_format($totalQty, 0, ',', '.') }} Pcs / Item</div>
            </td>
            <td class="kpi-cell" style="width: {{ $kpiWidth }};">
                <div class="kpi-title">Total Nominal / Omset</div>
                <div class="kpi-value kpi-value-green">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </td>
            @if(isset($totalSusulan) && $totalSusulan > 0)
            <td class="kpi-cell" style="width: {{ $kpiWidth }}; background-color: #fef9c3; border: 1px solid #fde047;">
                <div class="kpi-title" style="color: #854d0e;">Data Susulan Kemarin</div>
                <div class="kpi-value" style="color: #a16207;">{{ number_format($totalSusulan, 0, ',', '.') }} Transaksi</div>
            </td>
            @endif
            @if(isset($totalDihapus) && $totalDihapus > 0)
            <td class="kpi-cell" style="width: {{ $kpiWidth }}; background-color: #fee2e2; border: 1px solid #fca5a5;">
                <div class="kpi-title" style="color: #991b1b;">Data Dihapus</div>
                <div class="kpi-value" style="color: #dc2626;">{{ number_format($totalDihapus, 0, ',', '.') }} Transaksi</div>
            </td>
            @endif
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 95px;">Tanggal & Jam</th>
                <th style="width: 75px;">Nama</th>
                <th style="width: 140px;">Nama Produk</th>
                <th class="text-center" style="width: 45px;">Chip</th>
                <th class="text-center" style="width: 55px;">Status</th>
                <th class="text-right" style="width: 80px;">Postcal</th>
                <th class="text-center" style="width: 35px;">Qty</th>
                <th class="text-right" style="width: 85px;">Total Harga</th>
                <th>Rincian Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $isDeleted = method_exists($item, 'trashed') ? $item->trashed() : !is_null($item->deleted_at);
                    $isSusulan = !$isDeleted && (bool)($item->is_susulan ?? false);
                @endphp
                <tr class="{{ $isDeleted ? 'row-deleted' : ($isSusulan ? 'row-susulan' : '') }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ optional($item->created_at)->format('d/m/Y H:i') }}
                        @if($isDeleted && $item->deleted_at)
                            <div style="font-size: 8px; color: #b91c1c;">Hapus: {{ $item->deleted_at->format('d/m/y H:i') }}</div>
                        @elseif($isSusulan)
                            <div><span class="badge-status-susulan">⚠️ Susulan Kemarin</span></div>
                            <div style="font-size: 7.5px; color: #854d0e;">Diinput: {{ optional($item->updated_at ?? $item->created_at)->format('d/m H:i') }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge-nama">{{ $item->nama }}</span>
                    </td>
                    <td class="fw-bold {{ $isDeleted ? 'strike-text' : '' }}">
                        {{ $item->nama_produk }}
                        @if($isDeleted)
                            <span style="font-size: 8px; color: #dc2626; font-style: italic; display: block; font-weight: normal;">(Data Dihapus)</span>
                        @elseif($isSusulan)
                            <span style="font-size: 8px; color: #854d0e; font-style: italic; display: block; font-weight: normal;">(Input Susulan)</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge-chip {{ ($item->jenis_chip ?? 'KTTS') === 'KBTG' ? 'badge-chip-kbtg' : 'badge-chip-ktts' }}">
                            {{ $item->jenis_chip ?? 'KTTS' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($isDeleted)
                            <span class="badge-status-deleted">Dihapus</span>
                        @elseif(($item->status ?? 'pending') === 'sukses')
                            <span style="color: #15803d; font-weight: bold;">Sukses</span>
                        @elseif(($item->status ?? '') === 'gagal')
                            <span style="color: #dc2626; font-weight: bold;">Gagal</span>
                        @else
                            <span style="color: #d97706; font-weight: bold;">Pending</span>
                        @endif
                    </td>
                    <td class="text-right {{ $isDeleted ? 'strike-text' : '' }}">{{ $item->formatted_harga_qty }}</td>
                    <td class="text-center fw-bold {{ $isDeleted ? 'strike-text' : '' }}">{{ $item->qty }}</td>
                    <td class="text-right fw-bold {{ $isDeleted ? 'strike-text' : '' }}" style="{{ $isDeleted ? 'color: #dc2626;' : 'color: #15803d;' }}">
                        {{ $item->formatted_total_harga }}
                    </td>
                    <td class="deskripsi-cell">
                        <div>{{ \Illuminate\Support\Str::limit($item->deskripsi ?: '-', 120) }}</div>
                        @if($isDeleted && !empty($item->alasan_hapus))
                            <div class="alasan-hapus-box">
                                <strong>⚠️ Alasan Dihapus:</strong><br>
                                {{ $item->alasan_hapus }}
                                @if(!empty($item->deleted_by))
                                    <div style="font-size: 7.5px; color: #7f1d1d; margin-top: 2px;">Petugas: {{ $item->deleted_by }}</div>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data pendataan yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($items->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right" style="letter-spacing: 0.5px;">TOTAL TRANSAKSI AKTIF:</td>
                <td class="text-right">-</td>
                <td class="text-center">{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #15803d;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td style="font-size: 8px; color: #64748b; vertical-align: middle;">* Data dihapus dikecualikan dari total perhitungan</td>
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
