<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Minusan</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 25px;
                font-size: 11px;
            }

            h1 {
                text-align: center;
                margin-bottom: 10px;
            }

            hr {
                width: 80%;
                margin: 5px auto 20px;
                border: 1px solid #000;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                /* penting agar muat di satu halaman */
            }

            th,
            td {
                padding: 6px 5px;
                border: 1px solid #ddd;
                word-wrap: break-word;
                text-align: left;
            }

            thead {
                background-color: #4B3BFF;
                color: white;
            }

            th:nth-child(1) {
                width: 4%;
            }

            /* No */
            th:nth-child(2) {
                width: 10%;
            }

            /* Tanggal */
            th:nth-child(3) {
                width: 8%;
            }

            /* Server */
            th:nth-child(4) {
                width: 10%;
            }

            /* Nama */
            th:nth-child(5) {
                width: 8%;
            }

            /* SPL */
            th:nth-child(6) {
                width: 8%;
            }

            /* Produk */
            th:nth-child(7) {
                width: 10%;
            }

            /* Nomor */
            th:nth-child(8) {
                width: 10%;
            }

            /* Total */
            th:nth-child(9) {
                width: 5%;
            }

            /* Qty */
            th:nth-child(10) {
                width: 10%;
            }

            /* Total/Org */
            th:nth-child(11) {
                width: 17%;
            }

            /* Keterangan */
        </style>
    </head>

    <body>

        <h1>Data Minusan</h1>
        <hr>

        <div class="table-container">
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
        </div>

    </body>

</html>
