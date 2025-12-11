<table>
    <thead>
        <tr>
            <th width="20">No</th>
            <th width="20">Tanggal</th>
            <th width="20">Server</th>
            <th width="20">Nama</th>
            <th width="20">SPL</th>
            <th width="20">Produk</th>
            <th width="20">Nomor</th>
            <th width="20">Total</th>
            <th width="20">Qty</th>
            <th width="20">Total/Org</th>
            <th width="20">Keterangan</th>
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
                <td>{{ $item->total }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->total_per_orang }}</td>
                <td>{{ $item->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>