<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Tilangan</title>

    <style>
        @page {
            margin: 20px 25px;
        }

        :root {
            --primary: #4773d3ff;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .wrapper {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header .division {
            font-size: 12px;
        }

        .logo {
            width: 75px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 25px;
            font-weight: bold;
            color: var(--primary);
            margin-top: 8px;
        }

        .address {
            font-size: 10px;
            color: #000000ff;
            margin-bottom: 8px;
        }

        .subtitle {
            font-weight: bold;
            margin-top: 5px;
        }

        hr {
            border: none;
            border-top: 1.7px solid var(--primary);
            width: 65%;
            margin: 15px auto;
        }

        /* SUMMARY BOX */
        .summary {
            border: 1.5px solid var(--primary);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 18px;
        }

        .summary h4 {
            margin: 0 0 8px;
            color: var(--primary);
        }

        .summary table {
            width: 100%;
        }

        .summary td {
            padding: 5px 0;
            font-size: 11px;
        }

        .summary td:nth-child(1) {
            width: 55%;
        }

        .summary td:nth-child(2) {
            font-weight: bold;
            text-align: right;
        }

        /* TABLE RINCIAN */
        table.rinci {
            width: 85%;
            /* tabel tidak terlalu lebar */
            margin: 0 auto;
            /* tengah */
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: var(--primary);
            color: #fff;
        }

        th,
        td {
            border: 1px solid #d5d5d5;
            padding: 7px 6px;
        }

        /* Lebar kolom */
        .col-no {
            width: 8%;
            text-align: center;
        }

        .col-nama {
            width: 60%;
        }

        .col-total {
            width: 32%;
            text-align: right;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            margin-top: 12px;
            font-size: 9px;
            text-align: center;
            color: #555;
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <!-- HEADER -->
        <div class="header">
            <img src="{{ public_path('sbadmin2/img/logo_chika.png') }}" class="logo">

            <div class="title">Rekap Tilangan</div>

            <div class="division">Divisi Monitoring & Operasional</div>
            <div class="address">Jl. Taman Bahagia Makan Abri Raya No. 19 - 15224 Kota Tangerang
                Selatan Banten</div>

            <div class="subtitle">
                Periode: {{ $start->format('d F') }} - {{ $end->format('d F Y') }}
            </div>
        </div>

        <hr>

        <!-- SUMMARY -->
        <div class="summary">
            <h4>Ringkasan Rekap</h4>
            <table>
                <tr>
                    <td>Total Operator</td>
                    <td>{{ $minusan->count() }}</td>
                </tr>
                <tr>
                    <td>Total Tilangan Semua Operator</td>
                    <td>Rp {{ number_format($minusan->sum('total_tilangan'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- DATA TABLE -->
        <table class="rinci">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama</th>
                    <th class="col-total">Total Tilangan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($minusan as $item)
                <tr>
                    <td class="col-no">{{ $loop->iteration }}</td>
                    <td class="col-nama">{{ $item->nama }}</td>
                    <td class="col-total">
                        {{ number_format($item->total_tilangan, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- FOOTER -->
        <div class="footer">
            Dicetak pada {{ now()->format('d F Y H:i') }} • Sistem E-SLM Monitoring PT Chika Mulya Multimedia
        </div>

    </div>

</body>

</html>