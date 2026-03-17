<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $item->id }}</title>
    <style>
        body{font-family: DejaVu Sans, sans-serif; font-size:12px}
        .header{display:flex;justify-content:space-between;align-items:center}
        .items{width:100%;border-collapse:collapse;margin-top:10px}
        .items th, .items td{border:1px solid #ccc;padding:6px}
        .right{text-align:right}
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>Faktur / Invoice</h2>
            <div>Nomor: INV-{{ $item->id }}</div>
            <div>Tanggal: {{ $item->created_at->format('Y-m-d') }}</div>
        </div>
        <div>
            <strong>PT. CHIKA MULYA</strong>
            <div>Alamat perusahaan</div>
        </div>
    </div>

    <h4>Data Pemilik</h4>
    <div>Nama Pemilik: {{ $item->owner_name }}</div>
    <div>No. Rek: {{ $item->account_number }} ({{ $item->account_name }})</div>
    <div>Tanggal Pembelian: {{ optional($item->purchase_date)->format('Y-m-d H:i') }}</div>

    <h4>Barang</h4>
    <table class="items">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($item->items as $it)
            <tr>
                <td>{{ $it['name'] ?? '' }}</td>
                <td class="right">{{ $it['qty'] ?? 0 }}</td>
                <td class="right">{{ number_format((float)($it['price'] ?? 0),2) }}</td>
                <td class="right">{{ number_format(((float)($it['qty'] ?? 0) * (float)($it['price'] ?? 0)),2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="right"><strong>Total</strong></td>
                <td class="right"><strong>{{ number_format($item->total_amount,2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($item->invoice_text)
    <h4>Catatan / Bukti Faktur (teks)</h4>
    <pre style="white-space:pre-wrap">{{ $item->invoice_text }}</pre>
    @endif

    @if($item->invoice_path)
        @php $img = storage_path('app/public/'.$item->invoice_path); @endphp
        @if(file_exists($img))
            <h4>Lampiran Faktur</h4>
            <div><img src="{{ $img }}" style="max-width:100%;height:auto"/></div>
        @endif
    @endif

</body>
</html>
