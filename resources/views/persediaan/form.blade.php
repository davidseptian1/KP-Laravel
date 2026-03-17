@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h5>Form Permintaan Persediaan Stok</h5>
            <form method="post" action="{{ route('persediaan.store') }}" enctype="multipart/form-data">
                @csrf
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

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
                                <option value="{{ $bank->id }}">{{ $bank->nama_bank ?? $bank->nama ?? 'Bank '.$bank->id }} - {{ $bank->nomor_rekening ?? $bank->nomor_rek ?? '' }}</option>
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
                    <label class="form-label">Bukti Transfer (gambar) - klik area di bawah lalu tekan Ctrl+V untuk paste atau pilih file</label>
                    <div id="transfer-paste-area" contenteditable="true" style="border:1px dashed #ccc;padding:8px;min-height:80px;cursor:text;">Klik di sini lalu paste gambar (atau gunakan tombol pilih file)</div>
                    <div style="margin-top:.5rem;"><input type="file" name="transfer_proof" accept="image/*" class="form-control" id="transfer-proof-file"></div>
                    <div id="transfer-proof-preview" style="margin-top:.5rem;"></div>
                    <input type="hidden" name="transfer_proof_base64" id="transfer_proof_base64">
                </div>

                <div class="mb-3">
                    <label class="form-label">Bukti Faktur (opsional copy/paste gambar atau teks)</label>
                    <textarea name="invoice_text" id="invoice-text" class="form-control" rows="3" placeholder="Anda bisa paste teks atau gambar di sini (gambar akan disimpan sebagai lampiran)"></textarea>
                    <div class="mt-2">atau upload file: <input type="file" name="invoice_file" id="invoice-file" class="form-control"/></div>
                    <div id="invoice-file-preview" style="margin-top:.5rem;"></div>
                    <input type="hidden" name="invoice_file_base64" id="invoice_file_base64">
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
    // handle paste image and preview for transfer proof and invoice
    function readClipboardImageAndSetPreview(items, targetHiddenInputId, previewContainerId){
        for (const item of items) {
            if (item.type && item.type.indexOf('image') === 0) {
                const blob = item.getAsFile ? item.getAsFile() : null;
                if (blob) {
                    const reader = new FileReader();
                    reader.onload = function(e){
                        document.getElementById(targetHiddenInputId).value = e.target.result;
                        document.getElementById(previewContainerId).innerHTML = '<img src="'+e.target.result+'" style="max-width:200px;max-height:200px;"/>';
                    };
                    reader.readAsDataURL(blob);
                    return true;
                }
            }
        }
        return false;
    }

    document.addEventListener('paste', function(e){
        const active = document.activeElement;
        const clipboard = (e.clipboardData || window.clipboardData);
        if (!clipboard) return;

        // hanya tangani paste jika fokus di salah satu area yang didukung
        if (active && active.id === 'invoice-text') {
            const items = clipboard.items || [];
            if (readClipboardImageAndSetPreview(items, 'invoice_file_base64', 'invoice-file-preview')) {
                e.preventDefault();
                return;
            }
            // jika bukan gambar, biarkan teks masuk ke textarea normal
            return;
        }

        if (active && active.id === 'transfer-paste-area') {
            const items = clipboard.items || [];
            if (readClipboardImageAndSetPreview(items, 'transfer_proof_base64', 'transfer-proof-preview')) {
                e.preventDefault();
                return;
            }
            // jika teks ditempel di area transfer, biarkan teks ditampilkan (opsional)
            return;
        }
        // jika fokus bukan pada kedua area, abaikan paste (tidak mengambil gambar otomatis)
    });

    // file input change -> preview and set base64 for convenience (so both ways supported)
    function bindFilePreview(inputId, previewId, hiddenBase64Id){
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function(){
            const file = input.files && input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e){
                document.getElementById(previewId).innerHTML = '<img src="'+e.target.result+'" style="max-width:200px;max-height:200px;"/>';
                // set hidden base64 so server accepts even when file input not processed
                try{ document.getElementById(hiddenBase64Id).value = e.target.result; }catch(err){}
            };
            reader.readAsDataURL(file);
        });
    }

    bindFilePreview('transfer-proof-file','transfer-proof-preview','transfer_proof_base64');
    bindFilePreview('invoice-file','invoice-file-preview','invoice_file_base64');

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
