@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h5 class="fw-bold text-dark mb-4">Form Permintaan Persediaan Stok</h5>
            <form id="persediaan-form" method="post" action="{{ route('persediaan.store') }}" enctype="multipart/form-data">
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
                    <label class="form-label fw-bold text-dark">Nama Supplier <span class="text-danger">*</span></label>
                    <input name="company_name" class="form-control" placeholder="Contoh: PT Belanja Kuota / Supplier Utama" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">Nama Server <span class="text-danger">*</span></label>
                        <select name="division" class="form-select" required>
                            <option value="">-- Pilih Server --</option>
                            @foreach(($servers ?? []) as $srv)
                                <option value="{{ $srv->nama_server }}">{{ $srv->nama_server }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">-- Pilih Pembayaran --</option>
                            <option value="bank">Bank</option>
                            <option value="va">VA (Virtual Account)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Pilihan Bank (Bank/VA)</label>
                        <select name="bank_id" class="form-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->nama_bank ?? $bank->nama ?? 'Bank '.$bank->id }} - {{ $bank->nomor_rekening ?? $bank->nomor_rek ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Pilihan Cicilan / Tidak <span class="text-danger">*</span></label>
                        <select name="cicilan" class="form-select" required>
                            <option value="Tanpa Cicilan">Tanpa Cicilan</option>
                            <option value="Cicilan 1">Cicilan 1</option>
                            <option value="Cicilan 2">Cicilan 2</option>
                            <option value="Cicilan 3">Cicilan 3</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-dark">Tanggal PO <span class="text-danger">*</span></label>
                        <input type="date" name="po_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">No. Rekening</label>
                        <input name="account_number" class="form-control" placeholder="Contoh: 1234567890">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">A.N. Rekening</label>
                        <input name="account_name" class="form-control" placeholder="Contoh: PT Belanja Kuota">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">Bukti Faktur (Upload / Paste Ctrl+V Gambar)</label>
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
    function setImagePreview(containerId, base64HiddenId, fileInputId, dataUrl){
        const container = document.getElementById(containerId);
        if(!container) return;
        container.innerHTML = `
            <div style="position:relative;display:inline-block;">
                <img src="${dataUrl}" style="max-width:200px;max-height:200px;display:block;" />
                <button type="button" class="btn btn-sm btn-danger remove-preview" style="position:absolute;top:4px;right:4px;line-height:1;padding:2px 6px;border-radius:3px;">×</button>
            </div>
        `;
        try{ document.getElementById(base64HiddenId).value = dataUrl; }catch(e){}
        // bind remove
        const btn = container.querySelector('.remove-preview');
        if(btn){
            btn.addEventListener('click', function(){
                container.innerHTML = '';
                try{ document.getElementById(base64HiddenId).value = ''; }catch(e){}
                if(fileInputId){ try{ document.getElementById(fileInputId).value = ''; }catch(e){} }
            });
        }
    }

    function readClipboardImageAndSetPreview(items, targetHiddenInputId, previewContainerId){
        for (const item of items) {
            if (item.type && item.type.indexOf('image') === 0) {
                const blob = item.getAsFile ? item.getAsFile() : null;
                if (blob) {
                    const reader = new FileReader();
                    reader.onload = function(e){
                        setImagePreview(previewContainerId, targetHiddenInputId,
                            previewContainerId === 'transfer-proof-preview' ? 'transfer-proof-file' : 'invoice-file',
                            e.target.result);
                    };
                    reader.readAsDataURL(blob);
                    return true;
                }
            }
        }
        return false;
    }

    document.addEventListener('paste', function(e){
        const clipboard = (e.clipboardData || window.clipboardData);
        if (!clipboard) return;
        const items = clipboard.items || [];

        const active = document.activeElement;
        if (active && active.id === 'invoice-text') {
            if (readClipboardImageAndSetPreview(items, 'invoice_file_base64', 'invoice-file-preview')) {
                e.preventDefault();
                return;
            }
            return;
        }

        if (readClipboardImageAndSetPreview(items, 'transfer_proof_base64', 'transfer-proof-preview')) {
            e.preventDefault();
        }

        setTimeout(function(){
            const pasteArea = document.getElementById('transfer-paste-area');
            const transferHidden = document.getElementById('transfer_proof_base64');
            if (pasteArea && transferHidden) {
                const img = pasteArea.querySelector('img');
                if (img && img.src) {
                    transferHidden.value = img.src;
                    setImagePreview('transfer-proof-preview', 'transfer_proof_base64', 'transfer-proof-file', img.src);
                }
            }
        }, 100);
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
                setImagePreview(previewId, hiddenBase64Id, inputId, e.target.result);
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

    const addItemBtn = document.getElementById('add-item');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', ()=>addRow());
        addRow();
    }

    (function(){
        const form = document.getElementById('persediaan-form');
        if(!form) return;
        form.addEventListener('submit', function(e){
            const pasteArea = document.getElementById('transfer-paste-area');
            const transferHidden = document.getElementById('transfer_proof_base64');
            if (pasteArea && transferHidden) {
                const img = pasteArea.querySelector('img');
                if (img && img.src) {
                    transferHidden.value = img.src;
                }
            }

            const itemsTable = document.querySelector('#items-table tbody');
            let items = [];
            if (itemsTable) {
                const rows = Array.from(itemsTable.querySelectorAll('tr'));
                items = rows.map(r=>({
                    name: (r.querySelector('.item-name') && r.querySelector('.item-name').value) || '',
                    qty: (r.querySelector('.item-qty') && r.querySelector('.item-qty').value) || 0,
                    price: (r.querySelector('.item-price') && r.querySelector('.item-price').value) || 0,
                }));
            }
            const itemsJsonInput = document.getElementById('items-json');
            if (itemsJsonInput) {
                itemsJsonInput.value = JSON.stringify(items);
            }
        });
    })();
</script>

@endsection
