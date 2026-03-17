@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h5>Form Permintaan Persediaan Stok</h5>
            <form method="post" action="{{ route('persediaan.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama Pemilik</label>
                    <input name="owner_name" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">No. Rekening (Bank)</label>
                        <select name="bank_id" class="form-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->nama }} - {{ $bank->nomor_rekening ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">No. Rek</label>
                        <input name="account_number" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">A.N. Rekening</label>
                        <input name="account_name" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Pembelian</label>
                        <input type="datetime-local" name="purchase_date" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Penerimaan</label>
                        <input type="datetime-local" name="receive_date" class="form-control">
                    </div>
                </div>

                <h6>Barang yang dibeli</h6>
                <table class="table" id="items-table">
                    <thead><tr><th>Nama Barang</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-secondary" id="add-item">Tambah Baris</button>

                <div class="mt-3">
                    <label class="form-label">Atas Nama Input</label>
                    <input name="on_behalf" class="form-control">
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Bukti Transfer (gambar)</label>
                    <input type="file" name="transfer_proof" accept="image/*" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Bukti Faktur (opsional copy/paste)</label>
                    <textarea name="invoice_text" class="form-control" rows="3"></textarea>
                    <div class="mt-2">atau upload file: <input type="file" name="invoice_file" class="form-control"/></div>
                </div>

                <input type="hidden" name="items_json" id="items-json">

                <div class="mt-3">
                    <button class="btn btn-primary">Kirim Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function recalcRow(row){
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        row.querySelector('.item-subtotal').textContent = (qty*price).toFixed(2);
    }

    function addRow(name='', qty=1, price=0){
        const tbody = document.querySelector('#items-table tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input class="form-control item-name" value="${name}"></td>
            <td><input type="number" min="1" class="form-control item-qty" value="${qty}"></td>
            <td><input type="number" step="0.01" class="form-control item-price" value="${price}"></td>
            <td class="item-subtotal">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
        `;
        tbody.appendChild(tr);
        tr.querySelectorAll('.item-qty, .item-price').forEach(el=>el.addEventListener('input', ()=>recalcRow(tr)));
        tr.querySelector('.remove-row').addEventListener('click', ()=>{ tr.remove(); });
        recalcRow(tr);
    }

    document.getElementById('add-item').addEventListener('click', ()=>addRow());
    // add one initial row
    addRow();

    document.querySelector('form').addEventListener('submit', function(e){
        const rows = Array.from(document.querySelectorAll('#items-table tbody tr'));
        const items = rows.map(r=>({
            name: r.querySelector('.item-name').value,
            qty: r.querySelector('.item-qty').value,
            price: r.querySelector('.item-price').value,
        }));
        document.getElementById('items-json').value = JSON.stringify(items);
    });
</script>

@endsection
