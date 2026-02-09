<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Bulanan Data Minusan</title>

    <style>
        :root {
            --primary: #4773d3ff;
            /* warna tema premium */
        }

        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            font-size: 10px;
            color: #333;
        }

        /* HEADER PERUSAHAAN */

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 75px;
            margin-bottom: 5px;
        }
        
        .company-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-header .name {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary);
        }

        .company-header .division {
            font-size: 12px;
        }

        .company-header .address {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .doc-info {
            font-size: 10px;
            text-align: right;
            margin-bottom: 15px;
        }

        hr {
            border: 0;
            border-top: 2px solid var(--primary);
            margin: 15px 0;
        }

        /* TITLE */
        h2 {
            text-align: center;
            margin: 0;
            margin-bottom: 15px;
            color: var(--primary);
        }

        /* TABEL */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            /* BIAR TIDAK PECAH */
        }

        th {
            background-color: var(--primary);
            color: #fff;
            padding: 6px 4px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            white-space: nowrap;
            /* JUDUL TIDAK TURUN */
        }

        td {
            padding: 5px 4px;
            border: 1px solid #ccc;
            font-size: 10px;
        }

        /* Kolom yang wajar turun */
        td:nth-child(2),
        td:nth-child(4),
        td:nth-child(5),
        td:nth-child(6),
        td:nth-child(11) {
            white-space: normal;
        }

        /* Kolom No stabil */
        td:nth-child(1) {
            text-align: center;
            width: 5%;
            white-space: nowrap;
        }

        /* FOOTER */
        .footer {
            margin-top: 25px;
            font-size: 10px;
        }

        .signature-block {
            width: 100%;
            margin-top: 30px;
            font-size: 10px;
        }

        .signature-block td {
            border: none;
        }

        .copy {
            text-align: center;
            font-size: 9px;
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>

<body>

    <div class="company-header">
        <img src="{{ public_path('sbadmin2/img/logo_chika.png') }}" class="logo">
        <div class="name">PT Chika Mulya Multimedia</div>
        <div class="division">Divisi Monitoring & Operasional</div>
        <div class="address">Jl. Taman Bahagia Makan Abri Raya No. 19 - 15224 Kota Tangerang
            Selatan Banten</div>
    </div>

    <div class="doc-info">
        Dicetak : <b>{{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</b>
    </div>

    <hr>

    <h2>Rekap Bulanan Data Minusan</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Server</th>
                <th>Nama</th>
                <th>SPL</th>
                <th>Produk</th>
                <th>Nomor</th>
                <th>Total</th>
                <th>Qty</th>
                <th>Total/Org</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($minusan as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->server }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->spl }}</td>
                <td>{{ $item->produk }}</td>
                <td>{{ $item->nomor }}</td>
                <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                <td>{{ $item->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-block">
        <tr>
            <td>
                <b>Disetujui Oleh,</b><br><br><br>
                ___________________________<br>
                Nama Manager Operasional<br>
                <i>Manager Operasional</i>
            </td>

            <td style="text-align: right;">
                <b>Disusun Oleh,</b><br><br><br>
                ___________________________<br>
                Nama Staff<br>
                <i>Staf Administrasi</i>
            </td>
        </tr>
    </table>

    <div class="copy">
        © 2025 PT Chika Mulya Multimedia — Dokumen ini dicetak otomatis oleh sistem.
    </div>

</body>

</html>