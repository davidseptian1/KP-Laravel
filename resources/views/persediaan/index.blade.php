@extends('layouts/app')

@section('content')

<style>
    .persediaan-page .section-card {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }
    .persediaan-page .section-title { font-weight:600 }
    .persediaan-page .table-wrap { border:1px solid var(--bs-border-color,#dee2e6); border-radius:10px; overflow:auto }
</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Permintaan Persediaan</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Permintaan Persediaan Stok</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0 persediaan-page">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="section-title mb-0"><i class="ti ti-box me-1"></i>Permintaan Persediaan</div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersediaan">
                    <i class="ti ti-plus me-1"></i>Ajukan Persediaan
                </button>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <p class="mb-3">Gunakan tombol di kanan atas untuk membuat permintaan persediaan baru. Lampiran dapat ditempel dari clipboard atau di-upload.</p>

                <div class="table-wrap">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pemilik</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($records ?? collect()) as $r)
                                <tr class="js-persediaan-row" data-items='@json($r->items ?? [])' data-transfer-path="{{ $r->transfer_proof_path }}" data-invoice-path="{{ $r->invoice_path }}" data-id="{{ $r->id }}">
                                    <td>{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $r->owner_name }}</td>
                                    <td>{{ number_format($r->total_amount, 2, '.', ',') }}</td>
                                    <td>{{ $r->status ?? 'pending' }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary js-persediaan-open-detail" data-id="{{ $r->id }}">Lihat</button>
                                    </td>
                                </tr>

                                {{-- per-row modal moved outside table to keep valid HTML --}}
                            @endforeach
                            @if(empty($records) || $records->isEmpty())
                                <tr><td colspan="5" class="text-center text-muted">Belum ada permintaan persediaan.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Render detail modals outside the table to keep table markup valid --}}
                @foreach(($records ?? collect()) as $r)
                    <div class="modal fade" id="persediaanDetail{{ $r->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Permintaan #{{ $r->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Tanggal:</strong> {{ optional($r->created_at)->format('Y-m-d H:i') }}</p>
                                    <p><strong>Nama Pemilik:</strong> {{ $r->owner_name }}</p>
                                    <p><strong>Total:</strong> {{ number_format($r->total_amount,2,'.',',') }}</p>
                                    <p><strong>Items:</strong></p>
                                    <table class="table table-sm">
                                        <thead><tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                                        <tbody>
                                            @foreach($r->items ?? [] as $it)
                                                <tr>
                                                    <td>{{ $it['name'] ?? '' }}</td>
                                                    <td>{{ $it['qty'] ?? 0 }}</td>
                                                    <td>{{ number_format($it['price'] ?? 0,2,'.',',') }}</td>
                                                    <td>{{ number_format((($it['qty'] ?? 0) * ($it['price'] ?? 0)),2,'.',',') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <p><strong>Bukti Transfer:</strong></p>
                                    @if($r->transfer_proof_path && file_exists(storage_path('app/public/'.$r->transfer_proof_path)))
                                        <img src="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'transfer']) }}" style="max-width:240px;max-height:240px;display:block;" />
                                    @else
                                        <div class="text-muted">Tidak ada bukti transfer</div>
                                    @endif

                                    <p class="mt-3"><strong>Faktur / Lampiran:</strong></p>
                                    @if($r->invoice_path && file_exists(storage_path('app/public/'.$r->invoice_path)))
                                        <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'invoice']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka Faktur</a>
                                    @elseif($r->invoice_text)
                                        <pre class="small">{{ $r->invoice_text }}</pre>
                                    @else
                                        <div class="text-muted">Tidak ada faktur</div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Modal with the existing form --}}
<div class="modal fade" id="modalPersediaan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Permintaan Persediaan Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="persediaan-form" method="post" action="{{ route('persediaan.store') }}" enctype="multipart/form-data">
                    @csrf
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

                    <div class="mt-3 text-end">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // reuse the same JS logic previously used in the standalone form
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
                            previewContainerId === 'transfer-proof-preview' ? 'transfer-proof-file' : 'invoice-file-preview',
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
        const active = document.activeElement;
        const clipboard = (e.clipboardData || window.clipboardData);
        if (!clipboard) return;

        if (active && active.id === 'invoice-text') {
            const items = clipboard.items || [];
            if (readClipboardImageAndSetPreview(items, 'invoice_file_base64', 'invoice-file-preview')) {
                e.preventDefault();
                return;
            }
            return;
        }

        if (active && active.id === 'transfer-paste-area') {
            const items = clipboard.items || [];
            if (readClipboardImageAndSetPreview(items, 'transfer_proof_base64', 'transfer-proof-preview')) {
                e.preventDefault();
                return;
            }
            return;
        }
    });

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

    document.addEventListener('DOMContentLoaded', function(){
        document.getElementById('add-item').addEventListener('click', ()=>addRow());
        addRow();

        const form = document.getElementById('persediaan-form');
        if(!form) return;
        form.addEventListener('submit', function(e){
            const tbody = document.querySelector('#items-table tbody');
            if (tbody && tbody.children.length === 0) { addRow(); }
            const rows = Array.from(document.querySelectorAll('#items-table tbody tr'));
            const items = rows.map(r=>({
                name: (r.querySelector('.item-name') && r.querySelector('.item-name').value) || '',
                qty: (r.querySelector('.item-qty') && r.querySelector('.item-qty').value) || 0,
                price: (r.querySelector('.item-price') && r.querySelector('.item-price').value) || 0,
            }));
            document.getElementById('items-json').value = JSON.stringify(items);
        });
    });

    // Inline detail toggle for persediaan rows
    (function(){
        function closeOpenDetail() {
            const open = document.querySelector('.persediaan-detail-row');
            if (open) {
                const prev = open.previousElementSibling;
                if (prev) {
                    const prevBtn = prev.querySelector('.js-persediaan-open-detail');
                    if (prevBtn) prevBtn.textContent = 'Lihat';
                }
                open.remove();
            }
        }

        function buildDetailHtml(items, transferPath, invoicePath, id){
            let html = '<td colspan="5">';
            html += '<table class="table table-sm mb-2"><thead><tr><th>NAMA</th><th>QTY</th><th>HARGA</th><th>SUBTOTAL</th></tr></thead><tbody>';
            items.forEach(it => {
                const name = it.name || '';
                const qty = parseFloat(it.qty || 0);
                const price = parseFloat(it.price || 0);
                const subtotal = (qty*price).toFixed(2);
                html += `<tr><td>${name}</td><td>${qty}</td><td>${price.toFixed(2)}</td><td>${subtotal}</td></tr>`;
            });
            html += '</tbody></table>';

            html += '<div><strong>Bukti Transfer:</strong><div style="margin-top:.5rem;">';
            if (transferPath) {
                html += `<img src="/persediaan-stok/`+id+`/file/transfer" style="max-width:240px;max-height:240px;display:block;" onerror="this.style.display='none'"/>`;
            } else {
                html += '<div class="text-muted">Tidak ada bukti transfer</div>';
            }
            html += '</div></div>';

            html += '<div class="mt-3"><strong>Faktur / Lampiran:</strong> ';
            if (invoicePath) {
                html += `<a href="/persediaan-stok/`+id+`/file/invoice" target="_blank" class="btn btn-sm btn-outline-primary">Buka Faktur</a>`;
            } else {
                html += '<div class="text-muted">Tidak ada faktur</div>';
            }
            html += '</div>';

            html += '</td>';
            return html;
        }

        document.querySelectorAll('.js-persediaan-row').forEach(row => {
            row.addEventListener('click', function(e){
                // ignore clicks on buttons/links inside row
                if (e.target.closest('button') || e.target.closest('a')) return;
                const next = row.nextElementSibling;
                // if a detail row already open below this row, close it and toggle button text
                if (next && next.classList && next.classList.contains('persediaan-detail-row')) {
                    const btnHere = row.querySelector('.js-persediaan-open-detail');
                    if (btnHere) btnHere.textContent = 'Lihat';
                    next.remove();
                    return;
                }
                // close any other open detail
                closeOpenDetail();

                const items = JSON.parse(row.getAttribute('data-items') || '[]');
                const transferPath = row.getAttribute('data-transfer-path');
                const invoicePath = row.getAttribute('data-invoice-path');
                const id = row.getAttribute('data-id');

                const tr = document.createElement('tr');
                tr.className = 'persediaan-detail-row';
                tr.innerHTML = buildDetailHtml(items, transferPath, invoicePath, id);
                row.parentNode.insertBefore(tr, row.nextSibling);
                // set button to 'Tutup'
                const btnHere = row.querySelector('.js-persediaan-open-detail');
                if (btnHere) btnHere.textContent = 'Tutup';
                // scroll into view a bit
                tr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });

        // Also support clicking the small 'Lihat' button to toggle the inline detail
        document.querySelectorAll('.js-persediaan-open-detail').forEach(btn => {
            btn.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                const row = document.querySelector(`.js-persediaan-row[data-id="${id}"]`);
                if (row) row.click();
            });
        });
    })();
</script>
@endsection
