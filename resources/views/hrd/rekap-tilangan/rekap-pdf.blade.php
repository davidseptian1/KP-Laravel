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

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            position: relative;
        }

        /* WATERMARK */
        .watermark {
            position: fixed;
            top: 35%;
            left: 18%;
            font-size: 60px;
            color: rgba(180, 180, 180, 0.12);
            transform: rotate(-25deg);
            z-index: -1;
            font-weight: bold;
        }

        .wrapper {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 80px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #275DDB;
        }

        .address {
            font-size: 10px;
            color: #555;
            margin-top: 4px;
        }

        hr {
            border: none;
            border-top: 2px solid #275DDB;
            margin: 10px auto 20px;
            width: 65%;
        }

        .subtitle {
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }

        /* SUMMARY BOX */
        .summary {
            width: 100%;
            border: 1px solid #275DDB;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .summary h4 {
            margin: 0 0 8px;
            color: #275DDB;
        }

        .summary table td {
            padding: 4px 0;
            font-size: 11px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background: #275DDB;
            color: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 7px 6px;
        }

        th:nth-child(1) { width: 6%; }
        th:nth-child(2) { width: 70%; }
        th:nth-child(3) { width: 24%; text-align: right; }

        td:nth-child(3) { text-align: right; font-weight: bold; }

        /* FOOTER */
        .footer {
            margin-top: 10px;
            font-size: 9px;
            text-align: center;
            color: #555;
        }
        
    </style>
</head>

<body>

    <!-- Watermark -->
    <div class="watermark">PT CHIKA</div>

    <div class="wrapper">

        <!-- HEADER AREA -->
        <div class="header">
            <img src="{{ public_path('sbadmin2/img/logo_chika.png') }}" class="logo">

            <div class="title">Rekap Tilangan</div>

            <div class="address">
                PT Chika • Jl. Raya Industri No. 17, Cikarang • (021) 8899 2211  
            </div>

            <div class="subtitle">
                Periode : <b>{{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</b>
            </div>
        </div>

        <hr>

        <!-- SUMMARY -->
        <div class="summary">
            <h4>Ringkasan Rekap</h4>
            <table>
                <tr>
                    <td>Total Operator</td>
                    <td><b>{{ $minusan->count() }}</b></td>
                </tr>
                <tr>
                    <td>Total Tilangan</td>
                    <td><b>Rp {{ number_format($minusan->sum('total_tilangan'), 0, ',', '.') }}</b></td>
                </tr>
                <tr>
                    <td>Rata-rata Per Operator</td>
                    <td><b>Rp {{ number_format($minusan->avg('total_tilangan'), 0, ',', '.') }}</b></td>
                </tr>
            </table>
        </div>


        <!-- DATA TABLE -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Total Tilangan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($minusan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->total_tilangan ? number_format($item->total_tilangan, 0, ',', '.') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- FOOTER -->
        <div class="footer">
            Dicetak pada {{ now()->format('d F Y H:i') }} • Sistem E-SLM Monitoring PT Chika  
        </div>

    </div>

</body>

</html>
