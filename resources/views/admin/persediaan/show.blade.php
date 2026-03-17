@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h5>Detail Permintaan #{{ $item->id }}</h5>

            <p><strong>Nama Pemilik:</strong> {{ $item->owner_name }}</p>
            <p><strong>Rekening:</strong> {{ $item->account_number }} - {{ $item->account_name }}</p>
            <p><strong>Tanggal Pembelian:</strong> {{ optional($item->purchase_date)->format('Y-m-d H:i') }}</p>
            <p><strong>Tanggal Penerimaan:</strong> {{ optional($item->receive_date)->format('Y-m-d H:i') }}</p>

            <h6>Barang</h6>
            <ul>
                @foreach($item->items as $it)
                    <li>{{ $it['name'] }} — {{ $it['qty'] }} x {{ number_format($it['price'],2) }} = {{ number_format((floatval($it['qty'])*floatval($it['price'])),2) }}</li>
                @endforeach
            </ul>

            <p><strong>Total:</strong> {{ number_format($item->total_amount,2) }}</p>
            <p><strong>Atas Nama Input:</strong> {{ $item->on_behalf }}</p>

            <div class="row">
                <div class="col-md-6">
                    <h6>Bukti Transfer</h6>
                    @if($item->transfer_proof_path)
                        <img src="{{ asset('storage/'.$item->transfer_proof_path) }}" class="img-fluid" alt="bukti" />
                    @else
                        <p>Tidak ada bukti transfer</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6>Bukti Faktur</h6>
                    @if($item->invoice_text)
                        <pre style="white-space:pre-wrap">{{ $item->invoice_text }}</pre>
                    @endif
                    @if($item->invoice_path)
                        <p><a href="{{ asset('storage/'.$item->invoice_path) }}" target="_blank">Unduh/lihat file faktur</a></p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
